<?php
/**
 * Cmsbox.fr Magento 2 Monetico Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Monetico
 * @author    Cmsbox Development Team <contact@cmsbox.fr>
 * @copyright 2019 Cmsbox.fr all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Monetico\Model\Methods;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Module\Dir;
use Cmsbox\Monetico\Gateway\Config\Core;
use Cmsbox\Monetico\Helper\Tools;
use Cmsbox\Monetico\Gateway\Processor\Connector;
use Cmsbox\Monetico\Gateway\Config\Config;

class RedirectMethod extends \Magento\Payment\Model\Method\AbstractMethod
{

    protected $_code;
    protected $_isInitializeNeeded = true;
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCancel = true;
    protected $_canCapturePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $backendAuthSession;
    protected $cart;
    protected $urlBuilder;
    protected $_objectManager;
    protected $invoiceSender;
    protected $transactionFactory;
    protected $customerSession;
    protected $checkoutSession;
    protected $checkoutData;
    protected $quoteRepository;
    protected $quoteManagement;
    protected $orderSender;
    protected $sessionQuote;
    protected $config;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Cmsbox\Monetico\Gateway\Config\Config $config,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutData,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Api\CartManagementInterface $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->urlBuilder         = $urlBuilder;
        $this->backendAuthSession = $backendAuthSession;
        $this->cart               = $cart;
        $this->_objectManager     = $objectManager;
        $this->invoiceSender      = $invoiceSender;
        $this->transactionFactory = $transactionFactory;
        $this->customerSession    = $customerSession;
        $this->checkoutSession    = $checkoutSession;
        $this->checkoutData       = $checkoutData;
        $this->quoteRepository    = $quoteRepository;
        $this->quoteManagement    = $quoteManagement;
        $this->orderSender        = $orderSender;
        $this->sessionQuote       = $sessionQuote;
        $this->config             = $config;
        $this->_code              = Core::methodId(get_class());
    }

    /**
     * Check whether method is available
     *
     * @param  \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote) && null !== $quote;
    }

    /**
     * Prepare the request data.
     */
    public static function getRequestData($config, $storeManager, $methodId, $cardData = null, $entity = null, $moduleDirReader = null)
    {
        // Get the order entity
        $entity = ($entity) ? $entity : $config->cart->getQuote();

        // Include the vendor files
        require_once($moduleDirReader->getModuleDir('', Core::moduleName()) . '/Gateway/Vendor/MoneticoPaiement_Config.php');
        require_once($moduleDirReader->getModuleDir('', Core::moduleName()) . '/Gateway/Vendor/MoneticoPaiement_Ept.inc.php');

        // Get the customer language
        $lang = strtoupper($config->getCustomerLanguage());

        // Get the vendor instance
        $oEpt = new \MoneticoPaiement_Ept($lang);     		
        $oHmac = new \MoneticoPaiement_Hmac($oEpt); 
        
        // Prepare the parameters
        $sOptions = "";        
        $sReference = $config->createTransactionReference();
        $sMontant = number_format($entity->getGrandTotal(), 2);
        $sDevise  = Tools::getCurrencyCode($entity, $storeManager);        
        $sTexteLibre = Tools::getIncrementId($entity);
        $sDate = date("d/m/Y:H:i:s");
        $sEmail = $entity->getCustomerEmail();
        $sNbrEch = "";
        $sDateEcheance1 = "";
        $sMontantEcheance1 = "";
        $sDateEcheance2 = "";
        $sMontantEcheance2 = "";
        $sDateEcheance3 = "";
        $sMontantEcheance3 = "";
        $sDateEcheance4 = "";
        $sMontantEcheance4 = "";

        // Compute the HMAC
        $phase1go_fields = sprintf(
            MONETICOPAIEMENT_PHASE1GO_FIELDS,
            $oEpt->sNumero,
            $sDate,
            $sMontant,
            $sDevise,
            $sReference,
            $sTexteLibre,
            $oEpt->sVersion,
            $oEpt->sLangue,
            $oEpt->sCodeSociete, 
            $sEmail,
            $sNbrEch,
            $sDateEcheance1,
            $sMontantEcheance1,
            $sDateEcheance2,
            $sMontantEcheance2,
            $sDateEcheance3,
            $sMontantEcheance3,
            $sDateEcheance4,
            $sMontantEcheance4,
            $sOptions
        );
        $sMAC = $oHmac->computeHmac($phase1go_fields);

        // Prepare the array of parameters
        $params = [
            'url_paiement'   => $oEpt->sUrlPaiement,
            'version'        => $oEpt->sVersion,
            'TPE'            => $oEpt->sNumero,
            'date'           => $sDate,
            'montant'        => $sMontant . $sDevise,
            'reference'      => $sReference,
            'MAC'            => $sMAC,
            'url_retour'     => $oEpt->sUrlOK,
            'url_retour_ok'  => $oEpt->sUrlOK,
            'url_retour_err' => $oEpt->sUrlKO,
            'lgue'           => $oEpt->sLangue,
            'societe'        => $oEpt->sCodeSociete,
            'texte_libre'    => HtmlEncode($sTexteLibre),
            'mail'           => $sEmail,
            'nbrech'         => $sNbrEch,
            'dateech1'       => $sDateEcheance1,
            'montantech1'    => $sMontantEcheance1,
            'dateech2'       => $sDateEcheance2,
            'montantech2'    => $sMontantEcheance2,
            'dateech3'       => $sDateEcheance3,
            'montantech3'    => $sMontantEcheance3,
            'dateech4'       => $sDateEcheance4,
            'montantech4'    => $sMontantEcheance4
        ];

        return [
            'params' => $params,
            'seal' => $sMAC
        ];
    }

    /**
     * Checks if a response is valid.
     */
    public static function processResponse($config, $methodId, $asset, $moduleDirReader = null)
    {
        // Include the vendor files
        require_once($moduleDirReader->getModuleDir('', Core::moduleName()) . '/Gateway/Vendor/MoneticoPaiement_Config.php');
        require_once($moduleDirReader->getModuleDir('', Core::moduleName()) . '/Gateway/Vendor/MoneticoPaiement_Ept.inc.php');

        // Retrieve Variables posted by the remote server
        $MoneticoPaiement_bruteVars = getMethode();

        // Get the vendor instance
        $oEpt = new \MoneticoPaiement_Ept();     		
        $oHmac = new \MoneticoPaiement_Hmac($oEpt); 

        // Message Authentication
        $phase2back_fields = sprintf(
            MONETICOPAIEMENT_PHASE2BACK_FIELDS,
            $oEpt->sNumero,
            $MoneticoPaiement_bruteVars["date"],
            $MoneticoPaiement_bruteVars['montant'],
            $MoneticoPaiement_bruteVars['reference'],
            $MoneticoPaiement_bruteVars['texte-libre'],
            $oEpt->sVersion,
            $MoneticoPaiement_bruteVars['code-retour'],
            $MoneticoPaiement_bruteVars['cvx'],
            $MoneticoPaiement_bruteVars['vld'],
            $MoneticoPaiement_bruteVars['brand'],
            $MoneticoPaiement_bruteVars['status3ds'],
            $MoneticoPaiement_bruteVars['numauto'],
            $MoneticoPaiement_bruteVars['motifrefus'],
            $MoneticoPaiement_bruteVars['originecb'],
            $MoneticoPaiement_bruteVars['bincb'],
            $MoneticoPaiement_bruteVars['hpancb'],
            $MoneticoPaiement_bruteVars['ipclient'],
            $MoneticoPaiement_bruteVars['originetr'],
            $MoneticoPaiement_bruteVars['veres'],
            $MoneticoPaiement_bruteVars['pares']
        );

        // Prepare the transaction result
        $successCodes = ['payetest', 'paiement'];
        $isValid = $oHmac->computeHmac($phase2back_fields) == strtolower($MoneticoPaiement_bruteVars['MAC']);
        $isSuccess = in_array($MoneticoPaiement_bruteVars['code-retour'], $successCodes);
        
        // Prepare the receipt
        $receipt = ($isValid) 
        ? MONETICOPAIEMENT_PHASE2BACK_MACOK 
        : MONETICOPAIEMENT_PHASE2BACK_MACNOTOK . $phase2back_fields;

        // Return the result
        return [
            'isValid' => $isValid,
            'isSuccess' => $isSuccess,
            'receipt' => sprintf(
                MONETICOPAIEMENT_PHASE2BACK_RECEIPT,
                $receipt
            )
        ];
    }

    /**
     * Gets a transaction id.
     */
    public static function getTransactionId($config, $paymentObject)
    {
        return $paymentObject->getParam($config->base[Connector::KEY_TRANSACTION_ID_FIELD]);
    }
    
    /**
     * Determines if the method is active on frontend.
     */
    public static function isFrontend($config, $methodId)
    {
        // Get the quote entity
        $entity = $config->cart->getQuote();

        // Check the currency status
        $currencyAccepted = in_array(
            $entity->getQuoteCurrencyCode(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_CURRENCIES])
        );

        // Check the billing country status
        $countryAccepted = in_array(
            $entity->getBillingAddress()->getCountryId(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_COUNTRIES])
        ) && in_array(
            $entity->getShippingAddress()->getCountryId(),
            explode(',', $config->params[Core::moduleId()][Core::KEY_ACCEPTED_COUNTRIES])
        );

        return (int) (
            ((int)  $config->params[$methodId][Connector::KEY_ACTIVE] == 1)
            && $currencyAccepted
            && $countryAccepted
        );
    }
}

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
        include($moduleDirReader->getModuleDir('', Core::moduleName()) . '/Gateway/Vendor/MoneticoPaiement_Config');
        include($moduleDirReader->getModuleDir('', Core::moduleName()) . '/Gateway/Vendor/MoneticoPaiement_Ept.inc.php');

        // Get the customer language
        $lang = strtoupper($config->getCustomerLanguage());

        // Get the vendor instance
        $oTpe = new \MoneticoPaiement_Ept($lang);     		
        $oHmac = new \MoneticoPaiement_Hmac($oTpe); 
        
        // Prepare the parameters
        $sOptions = "";        
        $sReference = $config->createTransactionReference();
        $sMontant = number_format($entity->getGrandTotal(), 2);
        $sDevise  = Tools::getCurrencyCode($entity, $storeManager);        
        $sTexteLibre = "";
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
        $PHP1_FIELDS = sprintf(
            CMCIC_CGI1_FIELDS,
            $oTpe->sNumero,
            $sDate,
            $sMontant,
            $sDevise,
            $sReference,
            $sTexteLibre,
            $oTpe->sVersion,
            $oTpe->sLangue,
            $oTpe->sCodeSociete, 
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
        $sMAC = $oHmac->computeHmac($PHP1_FIELDS);

        // Prepare the array of parameters
        $params = [
            'url_paiement'   => $oTpe->sUrlPaiement,
            'version'        => $oTpe->sVersion,
            'TPE'            => $oTpe->sNumero,
            'date'           => $sDate,
            'montant'        => $sMontant . $sDevise,
            'reference'      => $sReference,
            'MAC'            => $sMAC,
            'url_retour'     => $oTpe->sUrlKO,
            'url_retour_ok'  => $oTpe->sUrlOK,
            'url_retour_err' => $oTpe->sUrlKO,
            'lgue'           => $oTpe->sLangue,
            'societe'        => $oTpe->sCodeSociete,
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
    public static function isValidResponse($config, $methodId, $asset)
    {
        // Get the vendor instance
        $fn = "\\" . $config->params[$methodId][Core::KEY_VENDOR];
        $paymentResponse = new $fn(Connector::getSecretKey($config));

        // Set the response
        $paymentResponse->setResponse($asset);
    
        // Return the validity status
        return $paymentResponse->isValid();
    }

    /**
     * Checks if a response is success.
     */
    public static function isSuccessResponse($config, $methodId, $asset)
    {
        // Get the vendor instance
        $fn = "\\" . $config->params[$methodId][Core::KEY_VENDOR];
        $paymentResponse = new $fn(Connector::getSecretKey($config));

        // Set the response
        $paymentResponse->setResponse($asset);

        // Return the success status
        return $paymentResponse->isSuccessful();
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

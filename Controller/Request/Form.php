<?php
/**
 * Cmsbox.fr Magento 2 Cmcic Payment.
 *
 * PHP version 7
 *
 * @category  Cmsbox
 * @package   Cmcic
 * @author    Cmsbox Development Team <contact@cmsbox.fr>
 * @copyright 2019 Cmsbox.fr all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.cmsbox.fr
 */

namespace Cmsbox\Cmcic\Controller\Request;
 
use Cmsbox\Cmcic\Gateway\Config\Core;
use Cmsbox\Cmcic\Gateway\Processor\Connector;

class Form extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var OrderHandlerService
     */
    protected $orderHandler;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * Normal constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Cmsbox\Cmcic\Model\Service\MethodHandlerService $methodHandler,
        \Cmsbox\Cmcic\Gateway\Config\Config $config,
        \Cmsbox\Cmcic\Model\Service\OrderHandlerService $orderHandler,
        \Cmsbox\Cmcic\Helper\Tools $tools,
        \Cmsbox\Cmcic\Helper\Watchdog $watchdog,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
    ) {
        parent::__construct($context);

        $this->pageFactory     = $pageFactory;
        $this->jsonFactory     = $jsonFactory;
        $this->methodHandler   = $methodHandler;
        $this->config          = $config;
        $this->orderHandler    = $orderHandler;
        $this->tools           = $tools;
        $this->watchdog        = $watchdog;
        $this->storeManager    = $storeManager;
        $this->moduleDirReader = $moduleDirReader;
    }
 
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            switch ($this->getRequest()->getParam('task')) {
                case 'block':
                    $response = $this->runBlock();
                    break;

                case 'charge':
                    $response = $this->runCharge();
                    break;

                default:
                    $response = $this->runBlock();
                    break;
            }

            return $this->jsonFactory->create()->setData(
                [Connector::KEY_RESPONSE => $response]
            );
        }

        return $this->jsonFactory->create()->setData(
            [
                $this->handleError(__('Invalid AJAX request in form controller.'))
            ]
        );
    }

    private function runCharge()
    {
        // Retrieve the expected parameters
        $methodId = $this->getRequest()->getParam('method_id', null);
        $cardData = $this->getRequest()->getParam('card_data', []);

        // Load the method instance if parameters are valid
        if ($methodId && !empty($methodId) && is_array($cardData) && !empty($cardData)) {
            // Load the method instance
            $methodInstance = $this->methodHandler->getStaticInstance($methodId);

            // Perform the charge request
            if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
                // Process the payment
                $paymentObject = $methodInstance::getRequestData(
                    $this->config,
                    $this->storeManager,
                    $methodId,
                    $cardData,
                    null,
                    $this->moduleDirReader
                );

                // Log the request
                $methodInstance::logRequestData(
                    Connector::KEY_REQUEST,
                    $this->watchdog,
                    $paymentObject
                );

                // Log the response
                $methodInstance::logResponseData(
                    Connector::KEY_RESPONSE,
                    $this->watchdog,
                    $paymentObject
                );

                // Process the response
                $isValidResponse = $methodInstance::isValidResponse(
                    $this->config,
                    $methodId,
                    $paymentObject
                );
                $isSuccessResponse = $methodInstance::isSuccessResponse(
                    $this->config,
                    $methodId,
                    $paymentObject
                );
                if ($isValidResponse && $isSuccessResponse) {
                    // Get the quote
                    $quote = $this->orderHandler->findQuote();

                    // Prepare the order data
                    $params = Connector::packData(
                        [
                            $this->config->base[Connector::KEY_ORDER_ID_FIELD] => $this->tools->getIncrementId($quote),
                            $this->config->base[
                                    Connector::KEY_TRANSACTION_ID_FIELD
                                ] => $methodInstance::getTransactionId(
                                $this->config,
                                $paymentObject
                            ),
                            $this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD] => isset(
                                    $response[$this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD]]
                                ) ? $response[$this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD]]
                                : $this->orderHandler->findCustomerEmail($quote),
                            $this->config->base[Connector::KEY_CAPTURE_MODE_FIELD] => $this->config->params[
                                $methodId][Connector::KEY_CAPTURE_MODE]
                        ]
                    );

                    // Place the order
                    $order = $this->orderHandler->placeOrder($params, $methodId);

                    // Perform after place order actions
                    $this->orderHandler->afterPlaceOrder($quote, $order);

                    // Return the result
                    return true;
                }
            }

            return $this->handleError(__('The transaction data is invalid.'));
        }
        
        return $this->handleError(__('Invalid request or payment method.'));
    }

    private function runBlock()
    {
        // Retrieve the expected parameters
        $methodId = $this->getRequest()->getParam('method_id', null);
        $template = $this->config->params[$methodId][Connector::KEY_FORM_TEMPLATE];

        // Create the block
        return $this->pageFactory->create()->getLayout()
            ->createBlock(Core::moduleClass() . '\Block\Payment\Form')
            ->setData('area', 'adminhtml')
            ->setTemplate(Core::moduleName() . '::payment_form/' . $template . '.phtml')
            ->setData('method_id', $methodId)
            ->setData('module_name', Core::moduleName())
            ->setData('template_name', $template)
            ->setData('is_admin', false)
            ->toHtml();
    }

    private function handleError($errorMessage)
    {
        $this->watchdog->logError($errorMessage);
        return $errorMessage;
    }
}

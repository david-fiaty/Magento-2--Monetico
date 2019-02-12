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

namespace Cmsbox\Cmcic\Controller\Response;
 
use Cmsbox\Cmcic\Gateway\Processor\Connector;

class Normal extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderHandlerService
     */
    protected $orderHandler;
    
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * Normal constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Cmsbox\Cmcic\Model\Service\OrderHandlerService $orderHandler,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Cmsbox\Cmcic\Helper\Watchdog $watchdog,
        \Cmsbox\Cmcic\Gateway\Config\Config $config,
        \Cmsbox\Cmcic\Model\Service\MethodHandlerService $methodHandler
    ) {
        parent::__construct($context);

        $this->orderHandler          = $orderHandler;
        $this->checkoutSession       = $checkoutSession;
        $this->messageManager        = $messageManager;
        $this->watchdog              = $watchdog;
        $this->config                = $config;
        $this->methodHandler         = $methodHandler;
    }
 
    public function execute()
    {
        // Get the request data
        $responseData = $this->getRequest()->getPostValue();

        // Log the response
        $this->watchdog->bark(
            Connector::KEY_RESPONSE,
            $responseData,
            $canDisplay = true,
            $canLog = false
        );

        // Load the method instance
        $methodId = $this->orderHandler->findMethodId();
        $methodInstance = $this->methodHandler->getStaticInstance($methodId);

        // Process the response
        if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
            if ($methodInstance::isValidResponse($this->config, $methodId, $responseData)) {
                if ($methodInstance::isSuccessResponse($this->config, $methodId, $responseData)) {
                    // Place order
                    $order = $this->orderHandler->placeOrder(Connector::packData($responseData), $methodId);

                    // Process the order result
                    if ($order && method_exists($order, 'getId') && (int)$order->getId() > 0) {
                        // Get the fields
                        $fields = Connector::unpackData(Connector::packData($responseData));

                        // Find the quote
                        $quote = $this->orderHandler->findQuote(
                            $fields[$this->config->base[Connector::KEY_ORDER_ID_FIELD]]
                        );

                        // Set the success redirection parameters
                        if (isset($quote) && (int)$quote->getId() > 0) {
                            // Perform after place order actions
                            $this->orderHandler->afterPlaceOrder($quote, $order);

                            // Display a success message
                            $this->messageManager->addSuccessMessage(__('The order was placed successfully.'));

                            // Redirect to the success page
                            return $this->_redirect('checkout/onepage/success', ['_secure' => true]);
                        } else {
                            $this->watchdog->logError(__('The quote could not be found.'));
                        }
                    } else {
                        $this->watchdog->logError(__('The order could not be created.'));
                    }
                } else {
                    $this->watchdog->logError(__('The transaction could not be processed. Please try again.'));
                }
            } else {
                $this->watchdog->logError(__('Invalid gateway response.'));
            }
        } else {
            $this->watchdog->logError(__('Invalid payment method.'));
        }

        // Redirect to the cart by default
        return $this->_redirect('checkout/cart', ['_secure' => true]);
    }
}

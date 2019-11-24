<?php
/**
 * Naxero.com Magento 2 Monetico Payment.
 *
 * PHP version 7
 *
 * @category  Naxero
 * @package   Monetico
 * @author    Naxero Development Team <contact@naxero.com>
 * @copyright 2019 Naxero.com all rights reserved
 * @license   https://opensource.org/licenses/mit-license.html MIT License
 * @link      https://www.naxero.com
 */

namespace Naxero\Monetico\Controller\Response;
 
use Naxero\Monetico\Gateway\Processor\Connector;

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
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * Normal constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Naxero\Monetico\Model\Service\OrderHandlerService $orderHandler,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Naxero\Monetico\Helper\Watchdog $watchdog,
        \Naxero\Monetico\Gateway\Config\Config $config,
        \Naxero\Monetico\Model\Service\MethodHandlerService $methodHandler,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface
    ) {
        parent::__construct($context);

        $this->orderHandler          = $orderHandler;
        $this->checkoutSession       = $checkoutSession;
        $this->messageManager        = $messageManager;
        $this->watchdog              = $watchdog;
        $this->config                = $config;
        $this->methodHandler         = $methodHandler;
        $this->moduleDirReader       = $moduleDirReader;
        $this->orderInterface        = $orderInterface;
    }
 
    public function execute()
    {
        // Get the request data
        $responseData = $this->getRequest()->getParams();

        // Log the response
        $this->watchdog->bark(
            Connector::KEY_RESPONSE,
            $responseData,
            $canDisplay = true,
            $canLog = false
        );

        // Load the method instance
        $methodId = $this->orderHandler->findMethodId();
        $methodInstance = $this->methodHandler::getStaticInstance($methodId);

        // Prepare the order id
        $orderId = $responseData[$this->config->base[Connector::KEY_ORDER_ID_FIELD]] ?? null;

        // Process the response
        if ($methodInstance && $orderId && (int) $orderId > 0) {
            // Get the order
            $order = $this->orderInterface->loadByIncrementId($orderId);

            // Process the order result
            if ($order && method_exists($order, 'getId') && (int)$order->getId() > 0) {
                // Find the quote
                $quote = $this->orderHandler->findQuote($orderId);

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
        }

        // Redirect to the cart by default
        return $this->_redirect('checkout/cart', ['_secure' => true]);
    }
}

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

namespace Cmsbox\Monetico\Model\Service;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Cmsbox\Monetico\Gateway\Processor\Connector;
use Cmsbox\Monetico\Gateway\Config\Core;

class OrderHandlerService
{
    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var Cart
     */
    protected $cart;

    /**
     * @var TransactionHandlerService
     */
    protected $transactionHandler;

    /**
     * @var QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var OrderSender
     */
    protected $orderSender;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var OrderInterface
     */
    protected $orderInterface;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var Config
     */
    protected $config;

    /**
     * OrderHandlerService constructor.
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Checkout\Model\Cart $cart,
        \Cmsbox\Monetico\Model\Service\TransactionHandlerService $transactionHandler,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Quote\Model\QuoteManagement $quoteManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Cmsbox\Monetico\Helper\Watchdog $watchdog,
        \Cmsbox\Monetico\Gateway\Config\Config $config
    ) {
        $this->cookieManager         = $cookieManager;
        $this->quoteFactory          = $quoteFactory;
        $this->cart                  = $cart;
        $this->transactionHandler    = $transactionHandler;
        $this->checkoutSession       = $checkoutSession;
        $this->customerSession       = $customerSession;
        $this->quoteManagement       = $quoteManagement;
        $this->orderSender           = $orderSender;
        $this->orderRepository       = $orderRepository;
        $this->orderInterface        = $orderInterface;
        $this->watchdog              = $watchdog;
        $this->config                = $config;
    }

    /**
     * Place an order
     */
    public function placeOrder($data, $methodId)
    {
        // Get the fields
        $fields = Connector::unpackData($data);

        // If a track id is available
        if (isset($fields[$this->config->base[Connector::KEY_ORDER_ID_FIELD]])) {
            // Check if the order exists
            $order = $this->orderInterface->loadByIncrementId(
                $fields[$this->config->base[Connector::KEY_ORDER_ID_FIELD]]
            );
    
            // Update the order
            if ((int) $order->getId() == 0) {

                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/xxx.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info((int) $order->getId());

                $order = $this->createOrder($fields, $methodId);

                $logger->info((int) $order->getId());

                return $order;
            }
        }

        return null;
    }

    /**
     * Create an order
     */
    public function createOrder($fields, $methodId)
    {
        try {
            // Find the quote
            $quote = $this->findQuote(
                $fields[$this->config->base[Connector::KEY_ORDER_ID_FIELD]]
            );

            // If there is a quote, create the order
            if ($quote->getId()) {
                // Prepare the inventory
                $quote->setInventoryProcessed(false);

                // Check for guest user quote
                if ($this->customerSession->isLoggedIn() === false) {
                    $quote = $this->prepareGuestQuote(
                        $quote,
                        $fields[$this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD]]
                    );
                }

                // Set the payment information
                $payment = $quote->getPayment();
                $payment->setMethod($methodId);
                $payment->save();

                // Create the order
                $order = $this->quoteManagement->submit($quote);

                // Update order status
                $isCaptureImmediate = $this->config->params[$methodId]
                [Connector::KEY_CAPTURE_MODE] == Connector::KEY_CAPTURE_IMMEDIATE;
                if ($isCaptureImmediate) {
                    // Create the transaction
                    $transactionId = $this->transactionHandler->createTransaction(
                        $order,
                        $fields,
                        Transaction::TYPE_CAPTURE,
                        $methodId
                    );
                } else {
                    // Update order status
                    $order->setStatus(
                        $this->config->params[Core::moduleId()][Connector::KEY_ORDER_STATUS_AUTHORIZED]
                    );

                    // Create the transaction
                    $transactionId = $this->transactionHandler->createTransaction(
                        $order,
                        $fields,
                        Transaction::TYPE_AUTH,
                        $methodId
                    );
                }

                // Save the order
                $this->orderRepository->save($order);

                // Send the email
                $this->orderSender->send($order);
                
                return $order;
            }
        } catch (\Exception $e) {
            $this->watchdog->logError($e);
            return false;
        }
    }

    /**
     * Sets the email for guest users
     */
    public function prepareGuestQuote($quote, $email = null)
    {
        // Retrieve the user email
        $guestEmail = ($email) ? $email : $this->findCustomerEmail();

        // Set the quote as guest
        $quote->setCustomerId(null)
            ->setCustomerEmail($guestEmail)
            ->setCustomerIsGuest(true)
            ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

        // Delete the cookie
        $this->cookieManager->deleteCookie(Connector::EMAIL_COOKIE_NAME);

        // Return the quote
        return $quote;
    }

    /**
     * Tasks after place order
     */
    public function afterPlaceOrder($quote, $order)
    {
        // Prepare session quote info for redirection after payment
        $this->checkoutSession
            ->setLastQuoteId($quote->getId())
            ->setLastSuccessQuoteId($quote->getId())
            ->clearHelperData();

        // Prepare session order info for redirection after payment
        $this->checkoutSession->setLastOrderId($order->getId())
            ->setLastRealOrderId($order->getIncrementId())
            ->setLastOrderStatus($order->getStatus());
    }

    /**
     * Find a customer email
     */
    public function findCustomerEmail($quote)
    {
        return $quote->getCustomerEmail()
        ?? $quote->getBillingAddress()->getEmail()
        ?? $this->cookieManager->getCookie(Connector::EMAIL_COOKIE_NAME);
    }

    /**
     * Find a method id
     */
    public function findMethodId()
    {
        return ($this->cookieManager->getCookie(Connector::METHOD_COOKIE_NAME))
        ? $this->cookieManager->getCookie(Connector::METHOD_COOKIE_NAME)
        : Core::moduleId() . '_' . Connector::KEY_REDIRECT_METHOD;
    }

    /**
     * Find a quote
     */
    public function findQuote($reservedIncrementId = null)
    {
        if ($reservedIncrementId) {
            return $this->quoteFactory
                ->create()->getCollection()
                ->addFieldToFilter('reserved_order_id', $reservedIncrementId)
                ->getFirstItem();
        }

        try {
            return $this->cart->getQuote();
        } catch (\Exception $e) {
            $this->watchdog->logError($e);
            return false;
        }
    }
}

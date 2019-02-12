<?php
/**
 * Checkout.com Magento 2 Payment module (https://www.checkout.com)
 *
 * Copyright (c) 2017 Checkout.com (https://www.checkout.com)
 * Author: David Fiaty | integration@checkout.com
 *
 * MIT License
 */

namespace Cmsbox\Cmcic\Observer\Backend;

use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order\Payment\Transaction;
use Cmsbox\Cmcic\Gateway\Processor\Connector;
use Cmsbox\Cmcic\Gateway\Config\Core;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
 
    /**
     * @var Session
     */
    protected $backendAuthSession;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TransactionHandlerService
     */
    protected $transactionHandler;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * OrderSaveBefore constructor.
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $backendAuthSession,
        \Cmsbox\Cmcic\Gateway\Http\Client $client,
        \Cmsbox\Cmcic\Gateway\Config\Config $config,
        \Cmsbox\Cmcic\Model\Service\TransactionHandlerService $transactionHandler,
        \Cmsbox\Cmcic\Helper\Watchdog $watchdog
    ) {
        $this->backendAuthSession = $backendAuthSession;
        $this->client             = $client;
        $this->config             = $config;
        $this->transactionHandler = $transactionHandler;
        $this->watchdog           = $watchdog;
    }
 
    /**
     * Observer execute function.
     */
    public function execute(Observer $observer)
    {
        if ($this->backendAuthSession->isLoggedIn()) {
            try {
                // Get the order
                $order = $observer->getEvent()->getOrder();

                // Get the payment info
                $paymentInfo = $order->getPayment()->getMethodInstance()->getInfoInstance();

                // Get the transaction id
                $transactionId = $paymentInfo->getData()
                [Connector::KEY_ADDITIONAL_INFORMATION]
                [Connector::KEY_TRANSACTION_INFO]
                [$this->config->base[Connector::KEY_TRANSACTION_ID_FIELD]];

                // Get the method id
                $methodId = $order->getPayment()->getMethodInstance()->getCode();

                // Prepare the order data
                $fields = [
                    $this->config->base[Connector::KEY_ORDER_ID_FIELD]       => $order->getIncrementId(),
                    $this->config->base[Connector::KEY_TRANSACTION_ID_FIELD] => $transactionId,
                    $this->config->base[Connector::KEY_CUSTOMER_EMAIL_FIELD] => $order->getCustomerEmail(),
                    $this->config->base[Connector::KEY_CAPTURE_MODE_FIELD]   => $this->config->params[$methodId]
                    [Connector::KEY_CAPTURE_MODE],
                    Core::KEY_METHOD_ID                                      => $methodId
                ];

                // Handle the transactions
                if ($this->config->params[$methodId][Connector::KEY_CAPTURE_MODE] == Connector::KEY_CAPTURE_IMMEDIATE) {
                    $captureTransactionId = $this->transactionHandler->createTransaction(
                        $order,
                        $fields,
                        Transaction::TYPE_CAPTURE,
                        $methodId
                    );
                } else {
                    $authorizationTransactionId = $this->transactionHandler->createTransaction(
                        $order,
                        $fields,
                        Transaction::TYPE_AUTH,
                        $methodId
                    );
                }
            } catch (\Exception $e) {
                $this->watchdog->logError($e);
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }

        return $this;
    }
}

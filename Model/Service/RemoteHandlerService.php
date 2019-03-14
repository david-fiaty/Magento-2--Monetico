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

class RemoteHandlerService
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * RemoteHandlerService constructor.
     */
    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Cmsbox\Monetico\Gateway\Config\Config $config,
        \Cmsbox\Monetico\Helper\Tools $tools,
        \Cmsbox\Monetico\Gateway\Http\Client $client,
        \Cmsbox\Monetico\Gateway\Processor\Connector $connector,
        \Cmsbox\Monetico\Helper\Watchdog $watchdog
    ) {
        $this->orderRepository    = $orderRepository;
        $this->config             = $config;
        $this->tools              = $tools;
        $this->client             = $client;
        $this->connector          = $connector;
        $this->watchdog           = $watchdog;
    }

    /**
     * Capture a transaction remotely.
     */
    public function captureRemoteTransaction($transaction, $amount, $payment = false)
    {
        try {
            // Get the method id
            $methodId = $transaction->getOrder()->getPayment()->getMethodInstance()->getCode();

            // Prepare the request URL
            $url = Connector::getApiUrl(
                'charge',
                $this->config,
                $methodId
            );
            $url .= 'charges/' . $transaction->getTxnId() . '/capture';

            // Get the order
            $order = $this->orderRepository->get($transaction->getOrderId());

            // Get the track id
            $trackId = $order->getIncrementId();

            // Prepare the request parameters
            $params = [
            'value' => $this->tools->formatAmount($amount),
            'trackId' => $trackId
            ];

            // Send the request
            $response = $this->client->getPostResponse($url, $params);

            // Process the response
            if ($this->tools->isChargeSuccess($response)) {
                // Update the void transaction
                if ($payment) {
                    $payment->setTransactionId($response['id']);
                    $payment->setParentTransactionId($transaction->getTxnId());
                    $payment->setIsTransactionClosed(1);
                    $payment->save();
                }

                return true;
            }
        } catch (\Exception $e) {
            $this->watchdog->logError($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The remote transaction could not be captured.')
            );
        }
    }

    /**
     * Void a transaction remotely.
     */
    public function voidRemoteTransaction($transaction, $amount, $payment = false)
    {
        try {
            // Get the method id
            $methodId = $transaction->getOrder()->getPayment()->getMethodInstance()->getCode();

            // Prepare the request URL
            $url = Connector::getApiUrl(
                'void',
                $this->config,
                $methodId
            );
            $url .= 'charges/' . $transaction->getTxnId() . '/void';

            // Get the order
            $order = $this->orderRepository->get($transaction->getOrderId());

            // Get the track id
            $trackId = $order->getIncrementId();

            // Prepare the request parameters
            $params = [
                'value' => $this->tools->formatAmount($amount),
                'trackId' => $trackId
            ];

            // Send the request
            $response = $this->client->getPostResponse($url, $params);

            // Process the response
            if ($this->tools->isChargeSuccess($response)) {
                // Update the void transaction
                if ($payment) {
                    $payment->setTransactionId($response['id']);
                    $payment->setParentTransactionId($transaction->getTxnId());
                    $payment->setIsTransactionClosed(1);
                    $payment->save();
                }

                return true;
            }
        } catch (\Exception $e) {
            $this->watchdog->logError($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The remote transaction could not be voided.')
            );
        }
    }

    /**
     * Refund a transaction remotely.
     */
    public function refundRemoteTransaction($transaction, $amount, $payment = false)
    {
        try {
            // Get the method id
            $methodId = $transaction->getOrder()->getPayment()->getMethodInstance()->getCode();

            // Prepare the request URL
            $url = Connector::getApiUrl(
                'refund',
                $this->config,
                $methodId
            );
            $url .= 'charges/' . $transaction->getTxnId() . '/refund';

            // Get the order
            $order = $this->orderRepository->get($transaction->getOrderId());

            // Get the track id
            $trackId = $order->getIncrementId();

            // Prepare the request parameters
            $params = [
                'value' => $this->tools->formatAmount($amount),
                'trackId' => $trackId
            ];

            // Send the request
            $response = $this->client->getPostResponse($url, $params);

            // Process the response
            if ($this->tools->isChargeSuccess($response)) {
                // Update the refund transaction
                if ($payment) {
                    $payment->setTransactionId($response['id']);
                    $payment->setParentTransactionId($transaction->getTxnId());
                    $payment->setIsTransactionClosed(1);
                    $payment->save();
                }

                return true;
            }
        } catch (\Exception $e) {
            $this->watchdog->logError($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The remote transaction could not be refunded.')
            );
        }
    }
}

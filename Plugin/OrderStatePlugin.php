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

namespace Cmsbox\Monetico\Plugin;

use Magento\Sales\Model\Order;
use Cmsbox\Monetico\Gateway\Config\Core;

class OrderStatePlugin
{
    /**
     * @var Tools
     */
    protected $tools;

    /**
     * @var Config
     */
    protected $config;

    /**
     * OrderStatePlugin constructor.
     */
    public function __construct(
        \Cmsbox\Monetico\Helper\Tools $tools,
        \Cmsbox\Monetico\Gateway\Config\Config $config
    ) {
        $this->tools  = $tools;
        $this->config = $config;
    }

    public function aroundExecute(
        \Magento\Sales\Model\Order\Payment\State\CommandInterface $subject,
        \Closure $proceed,
        \Magento\Sales\Api\Data\OrderPaymentInterface $payment,
        $amount,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        // Prepare the result
        $result = $proceed($payment, $amount, $order);

        // Build the module id from the payment method
        $methodCode = $payment->getMethodInstance()->getCode();
        $members = explode('_', $methodCode);
        $moduleId = isset($members[0]) && isset($members[1])
        ? $members[0] . $members[1] : '';

        // Check the payment method and update order status
        if (!empty($moduleId) && $moduleId == Core::moduleId()) {
            if ($order->getState() == Order::STATE_PROCESSING) {
                $order->setStatus($this->config->params[Core::moduleId()][Core::KEY_ORDER_STATUS_CAPTURED]);
            }
        }

        return $result;
    }
}

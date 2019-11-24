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

namespace Naxero\Monetico\Model\Adminhtml\Source;

use Magento\Sales\Model\Order\Payment\Transaction;

class InvoiceCreation implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Transaction::TYPE_CAPTURE,
                'label' => __('Capture')
            ],
            [
                'value' => Transaction::TYPE_AUTH,
                'label' => 'Authorisation'
            ],
        ];
    }
}

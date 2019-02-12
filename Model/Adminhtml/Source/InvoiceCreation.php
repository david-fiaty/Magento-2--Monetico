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

namespace Cmsbox\Cmcic\Model\Adminhtml\Source;

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

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

use Cmsbox\Cmcic\Gateway\Processor\Connector;

class CaptureMode implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Possible capture modes
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => Connector::KEY_CAPTURE_IMMEDIATE,
                'label' => __('Immediate'),
            ],
            [
                'value' => Connector::KEY_CAPTURE_DEFERRED,
                'label' => __('Deferred'),
            ],
            [
                'value' => Connector::KEY_CAPTURE_MANUAL,
                'label' => __('Validation'),
            ],
        ];
    }
}

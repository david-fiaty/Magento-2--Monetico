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

use Naxero\Monetico\Gateway\Processor\Connector;

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

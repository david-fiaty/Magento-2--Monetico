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

class Environment implements \Magento\Framework\Option\ArrayInterface
{

    const ENVIRONMENT_PROD = 'prod';
    const ENVIRONMENT_TEST = 'test';

    /**
     * Possible environment types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::ENVIRONMENT_TEST,
                'label' => __('Test'),
            ],
            [
                'value' => self::ENVIRONMENT_PROD,
                'label' => __('Production'),
            ],
        ];
    }
}

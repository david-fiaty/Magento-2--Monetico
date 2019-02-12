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

namespace Cmsbox\Cmcic\Helper;

class Tools
{

    /**
     * Returns the increment id of an order or a quote.
     *
     * @return string
     */
    public static function getIncrementId($entity)
    {
        return method_exists($entity, 'getIncrementId')
        ? $entity->getIncrementId()
        : $entity->reserveOrderId()->save()->getReservedOrderId();
    }

    /**
     * Returns the currency code of an order or a quote.
     *
     * @return string
     */
    public static function getCurrencyCode($entity, $storeManager)
    {
        try {
            if (method_exists($entity, 'getQuoteCurrencyCode')) {
                return $entity->getQuoteCurrencyCode();
            } elseif (method_exists($entity, 'getOrderCurrencyCode')) {
                return $entity->getOrderCurrencyCode();
            } else {
                return $storeManager->getStore()->getCurrentCurrency()->getCode();
            }
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}

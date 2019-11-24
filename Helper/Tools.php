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

namespace Naxero\Monetico\Helper;

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

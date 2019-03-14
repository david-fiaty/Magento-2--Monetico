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
 
namespace Cmsbox\Monetico\Gateway\Config;

class Core
{
    const CODE = 'cmsbox_monetico';
    const CODE_ADMIN = 'cmsbox_monetico_admin_method';
    const CODE_FORM = 'cmsbox_monetico_form_method';
    const KEY_METHOD_ID = 'method_id';
    const KEY_VERIFY_3DS = 'verify_3ds';
    const KEY_NORMAL_RETURN_URL = 'normal_return_url';
    const KEY_AUTOMATIC_RESPONSE_URL = 'automatic_response_url';
    const KEY_VERSION = 'key_version';
    const KEY_BYPASS_RECEIPT = 'bypass_receipt';
    const KEY_INVOICE_CREATION = 'invoice_creation';
    const KEY_ACCEPTED_CURRENCIES = 'accepted_currencies';
    const KEY_ACCEPTED_COUNTRIES = 'accepted_countries';
    const KEY_SUPPORTED_CURRENCIES = 'supported_currencies';
    const KEY_INTERFACE_VERSION_CHARGE = 'interface_version_charge';
    const KEY_CARD_NUMBER = 'number';
    const KEY_CARD_CVV = 'cvv';
    const KEY_CARD_MONTH = 'month';
    const KEY_CARD_YEAR = 'year';
    const KEY_URL_CHARGE = 'url_charge';
    const KEY_URL_VOID = 'url_void';
    const KEY_URL_REFUND = 'url_refund';
    const KEY_CHARGE_SUFFIX = 'charge_suffix';
    const KEY_AUTO_GENERATE_INVOICE = 'auto_generate_invoice';
    
    /**
     * Build a payment method ID.
     */
    public static function methodId($classPath)
    {
        $members = explode("\\", $classPath);
        $arr = preg_split('/(?<=[a-z])(?=[A-Z])/x', $members[4]);
        return self::moduleId() . '_' . strtolower($arr[0]) . '_' . strtolower($arr[1]);
    }

    /**
     * Build a payment method name from method ID.
     */
    public static function methodName($methodId)
    {
        $members = explode("_", $methodId);
        return ucfirst($members[2]) . ucfirst($members[3]);
    }


    /**
     * Get the module id from folder.
     */
    public static function moduleId()
    {
        $members = explode("\\", get_class());
        return (strtolower($members[0]) . '_' . strtolower($members[1]));
    }

    /**
     * Get the module name from folder.
     */
    public static function moduleName()
    {
        $members = explode("\\", get_class());
        return ($members[0] . '_' . $members[1]);
    }

    /**
     * Get the module path from folder.
     */
    public static function moduleClass()
    {
        $members = explode("\\", get_class());
        return ($members[0] . "\\" . $members[1]);
    }
    
    /**
     * Get the module name from folder.
     */
    public static function moduleLabel()
    {
        $members = explode("\\", get_class());
        return ($members[0] . ' ' . $members[1]);
    }
}

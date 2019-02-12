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
 
namespace Cmsbox\Cmcic\Gateway\Processor;

class Connector
{
    const KEY_ENVIRONMENT = 'environment';
    const KEY_SIMU_MERCHANT_ID = 'simu_merchant_id';
    const KEY_TEST_MERCHANT_ID = 'test_merchant_id';
    const KEY_PROD_MERCHANT_ID = 'prod_merchant_id';
    const KEY_SIMU_SECRET_KEY = 'simu_secret_key';
    const KEY_TEST_SECRET_KEY = 'test_secret_key';
    const KEY_PROD_SECRET_KEY = 'prod_secret_key';
    const KEY_REQUEST = 'request';
    const KEY_RESPONSE = 'response';
    const KEY_LOGGING = 'logging';
    const KEY_RESPONSE_ERROR = 'error';
    const KEY_RESPONSE_SUCCESS = 'success';
    const KEY_CAPTURE_MODE_FIELD = 'capture_mode_field';
    const KEY_CUSTOMER_EMAIL_FIELD = 'customer_email_field';
    const KEY_ORDER_ID_FIELD = 'order_id_field';
    const KEY_TRANSACTION_ID_FIELD = 'transaction_id_field';
    const KEY_CAPTURE_MODE = 'capture_mode';
    const KEY_CAPTURE_DAY = 'capture_day';
    const KEY_CAPTURE_IMMEDIATE = 'IMMEDIATE';
    const KEY_CAPTURE_DEFERRED = 'AUTHOR_CAPTURE';
    const KEY_CAPTURE_MANUAL = 'VALIDATION';
    const KEY_ORDER_STATUS_AUTHORIZED = 'order_status_authorized';
    const KEY_ORDER_STATUS_CAPTURED = 'order_status_captured';
    const KEY_ORDER_STATUS_REFUNDED = 'order_status_refunded';
    const KEY_ORDER_STATUS_FLAGGED = 'order_status_flagged';
    const KEY_TRANSACTION_INFO = 'transaction_info';
    const KEY_ADDITIONAL_INFORMATION = 'additional_information';
    const KEY_ACTIVE = 'active';
    const KEY_REDIRECT_METHOD = 'redirect_method';
    const KEY_FORM_TEMPLATE = 'form_template';
    const EMAIL_COOKIE_NAME = 'guestEmail';
    const METHOD_COOKIE_NAME = 'methodId';
        
    /**
     * Turns a data response string into an array.
     */
    public static function unpackData($response)
    {
        // Get the parameters
        $params = $response;

        // Prepare the separators
        $separator1 = '|';
        $separator2 = '=';

        // Prepare the output array
        $output = [];

        // Process first level data
        $arr = explode($separator1, $params);

        // Process second level data
        if (is_array($arr) && !empty($arr)) {
            foreach ($arr as $row) {
                $members = explode($separator2, $row);
                $output[$members[0]] = $members[1];
            }

            return $output;
        }

        return $arr;
    }

    /**
     * Turns a data request array into a string.
     */
    public static function packData($arr)
    {
        $output = [];
        foreach ($arr as $key => $val) {
            $output[] = $key . '=' . $val;
        }

        return implode('|', $output);
    }

    /**
     * Builds the API URL.
     *
     * @return string
     */
    public static function getApiUrl($action, $config, $methodId)
    {
        $mode = $config->params[\Cmsbox\Cmcic\Gateway\Config\Core::moduleId()][self::KEY_ENVIRONMENT];
        $path = 'api_url' . '_' . $mode . '_' . $action;
        return $config->params[$methodId][$path];
    }
  
    /**
     * Returns the merchant ID.
     *
     * @return string
     */
    public static function getMerchantId($config)
    {
        switch ($config->base[self::KEY_ENVIRONMENT]) {
            case 'simu':
                $id = $config->base[self::KEY_SIMU_MERCHANT_ID];
                break;

            case 'test':
                $id = $config->base[self::KEY_TEST_MERCHANT_ID];
                break;

            case 'prod':
                $id = $config->base[self::KEY_PROD_MERCHANT_ID];
                ;
                break;
        }

        return (string) $id;
    }

    /**
     * Returns the active secret key.
     *
     * @return string
     */
    public static function getSecretKey($config)
    {
        // Return the secret key
        switch ($config->base[self::KEY_ENVIRONMENT]) {
            case 'simu':
                $key = $config->params[\Cmsbox\Cmcic\Gateway\Config\Core::moduleId()][self::KEY_SIMU_SECRET_KEY];
                break;

            case 'test':
                $key = $config->params[\Cmsbox\Cmcic\Gateway\Config\Core::moduleId()][self::KEY_TEST_SECRET_KEY];
                break;

            case 'prod':
                $key = $config->params[\Cmsbox\Cmcic\Gateway\Config\Core::moduleId()][self::KEY_PROD_SECRET_KEY];
                break;
        }

        return $key;
    }

    /**
     * Returns the billing address.
     */
    public static function getBillingAddress($entity, $config)
    {
        // Retrieve the address object
        $address = $entity->getBillingAddress();

        // Return the formatted array
        return [
            'billingAddress.street'  => implode(', ', $address->getStreet()),
            'billingAddress.city'    => $address->getCity(),
            'billingAddress.country' => $config->getCountryCodeA2A3($address->getCountryId()),
            'billingAddress.zipCode' => $address->getPostcode(),
            'billingContact.email'   => $entity->getCustomerEmail(),
            'billingAddress.state'   => !empty($address->getRegionCode()) ? $address->getRegionCode() : '',
        ];
    }

    /**
     * Returns the shipping address.
     */
    public static function getShippingAddress($entity, $config)
    {
        // Retrieve the address object
        $address = $entity->getBillingAddress();

        // Return the formatted array,
        return [
            'customerAddress.street'  => implode(', ', $address->getStreet()),
            'customerAddress.city'    => $address->getCity(),
            'customerAddress.country' => $config->getCountryCodeA2A3($address->getCountryId()),
            'customerAddress.zipCode' => $address->getPostcode(),
            'customerAddress.state'   => !empty($address->getRegionCode()) ? $address->getRegionCode() : '',
            'customerContact.email'   => $entity->getCustomerEmail()
        ];
    }
}

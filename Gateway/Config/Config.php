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
 
namespace Naxero\Monetico\Gateway\Config;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Module\Dir;
use Naxero\Monetico\Gateway\Processor\Connector;
use Naxero\Monetico\Gateway\Config\Core;

class Config
{
    
    const KEY_DEFAULT_LANGUAGE = 'en';

    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * @var Parser
     */
    protected $xmlParser;

    /**
     * @var Csv
     */
    protected $csvParser;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Cart
     */
    public $cart;

    /**
     * @var Core
     */
    protected $core;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * @var Resolver
     */
    protected $localeResolver;

    /**
     * @var Array
     */
    public $params = [];

    /**
     * @var Array
     */
    public $base = [];

    /**
     * Config constructor.
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Magento\Framework\Xml\Parser $xmlParser,
        \Magento\Framework\File\Csv $csvParser,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Naxero\Monetico\Model\Service\MethodHandlerService $methodHandler,
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        $this->moduleDirReader = $moduleDirReader;
        $this->xmlParser       = $xmlParser;
        $this->csvParser       = $csvParser;
        $this->scopeConfig     = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->cart            = $cart;
        $this->storeManager    = $storeManager;
        $this->methodHandler   = $methodHandler;
        $this->localeResolver  = $localeResolver;
        
        // Load the module config file
        $this->loadConfig();
    }

    /**
     * Loads the module configuration parameters.
     */
    public function loadConfig()
    {
        try {
            // Prepare the output container
            $output = [];

            // Get the config file
            $filePath = $this->moduleDirReader->getModuleDir(Dir::MODULE_ETC_DIR, Core::moduleName()) . '/config.xml';
            $fileData = $this->xmlParser->load($filePath)->xmlToArray()['config']['_value']['default'];

            // Set the base parameters array
            $this->base = $this->buildBase($fileData);

            // Get the config array
            $configArray = $fileData['payment'] ?? [];

            // Get the configured values
            if (!empty($configArray)) {
                foreach ($configArray as $methodId => $params) {
                    $lines = [];
                    foreach ($params as $key => $val) {
                        // Check a database value
                        $dbValue = $this->scopeConfig->getValue(
                            'payment/' . $methodId . '/' . $key,
                            ScopeInterface::SCOPE_STORE
                        );

                        // Convert the value to string for empty testing
                        $testValue = stripslashes(trim(var_export($dbValue, true), "'"));

                        // Assign the value or override with db value
                        if (!empty($testValue)) {
                            $lines[$key] = $testValue;
                        } else {
                            $lines[$key] = $val;
                        }
                    }

                    $output[$methodId] = $lines;
                }
            }

            // Set the payment methods config array
            $this->params = $output;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Builds the base module parameters.
     *
     * @return bool
     */
    public function buildBase($fileData)
    {
        try {
            $output = [];
            $exclude = explode(',', $fileData['base']['exclude']);

            foreach ($fileData['payment'][Core::moduleId()] as $key => $val) {
                if (!in_array($key, $exclude)) {
                    // Check a database value
                    $dbValue = $this->scopeConfig->getValue(
                        'payment/' . Core::moduleId() . '/' . $key,
                        ScopeInterface::SCOPE_STORE
                    );

                    // Convert the value to string for empty testing
                    $testValue = stripslashes(trim(var_export($dbValue, true), "'"));

                    // Assign the value or override with db value
                    if (!empty($testValue)) {
                        $output[$key] = $testValue;
                    } else {
                        $output[$key] = $val;
                    }
                }
            }
            
            unset($fileData['base']['exclude']);

            return array_merge($fileData['base'], $output);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Get supported currencies.
     *
     * @return string
     */
    public function getSupportedCurrencies()
    {
        try {
            $output = [];
            $arr = explode(';', $this->base[Core::KEY_SUPPORTED_CURRENCIES]);
            foreach ($arr as $val) {
                $parts = explode(',', $val);
                $output[$parts[0]] = $parts[1];
            }

            return $output;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    /**
     * Provides the frontend config parameters.
     *
     * @return string
     */
    public function getFrontendConfig()
    {
        try {
            // Prepare the output
            $output = [];

            // Get request data for each method
            foreach ($this->params as $methodId => $val) {
                $arr = explode('_', $methodId);
                if ($this->methodIsValid($arr, $methodId, $val)) {
                    $methodInstance = $this->methodHandler::getStaticInstance($methodId);
                    if ($methodInstance && $methodInstance::isFrontend($this, $methodId)) {
                        $output[$methodId] = $val;
                        $output[$methodId][Connector::KEY_ACTIVE] = $methodInstance::isFrontend($this, $methodId);
                        if (isset($val['load_request_data']) && (int) $val['load_request_data'] == 1) {
                            $output[$methodId]['api_url'] = Connector::getApiUrl('charge', $this, $methodId);
                            $output[$methodId]['request_data'] = $methodInstance::getRequestData(
                                $this,
                                $this->storeManager,
                                $methodId,
                                null,
                                null,
                                $this->moduleDirReader
                            );
                        }
                    }
                }
            }

            // Return the formatted config array
            return [
                'payment' => [
                    Core::moduleId() => array_merge(
                        $output,
                        $this->base
                    )
                ]
            ];
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

    public function methodIsValid($arr, $key, $val)
    {
        return isset($arr[2]) && isset($arr[3])
        && isset($val['can_use_internal']) && (int) $val['can_use_internal'] != 1
        && !in_array($key, $arr);
    }

    /**
     * Returns the transaction reference.
     *
     * @return string
     */
    public function createTransactionReference()
    {
        return (string) time();
    }

    /**
     * Retrieves the customer language.
     */
    public function getCustomerLanguage()
    {
        $lang = explode('_', $this->localeResolver->getLocale());
        return (isset($lang[0]) && !empty($lang[0])) ? $lang[0] : self::KEY_DEFAULT_LANGUAGE;
    }

    /**
     * Formats an amount for a gateway request.
     */
    public function formatAmount($amount)
    {
        return (int) (number_format($amount, 2))*100;
    }

    /**
     * Retrieves an Alpha 3 country code from Alpha 2 code.
     */
    public function getCountryCodeA2A3($val)
    {
        try {
            // Get the csv file path
            $path = $this->moduleDirReader->getModuleDir('', Core::moduleName()) . '/Model/Files/countries.csv';
            
            if (is_file($path)) {
                // Read the countries
                $countries = $this->csvParser->getData($path);

                // Find the wanted result
                $res = array_filter(
                    $countries,
                    function ($arr) use ($val) {
                        return $arr[1] == $val;
                    }
                );

                // Reset the array ke
                $res = array_merge(array(), $res);
                if (isset($res[0]) && !empty($res)) {
                    return $res[0][2];
                }
            }
        
            return null;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}

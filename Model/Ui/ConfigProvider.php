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

namespace Cmsbox\Cmcic\Model\Ui;

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * ConfigProvider constructor.
     */
    public function __construct(
        \Cmsbox\Cmcic\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Send the configuration to the frontend
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->config->getFrontendConfig();
    }
}

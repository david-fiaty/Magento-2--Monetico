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

namespace Cmsbox\Cmcic\Block\Payment;

class Form extends \Magento\Framework\View\Element\Template
{
    /**
     * @var FormHandlerService
     */
    public $formHandler;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var AssetRepository
     */
    public $assetRepository;

    /**
     * @var Array
     */
    public $months;

    /**
     * @var Array
     */
    public $years;

    /**
     * Form constructor.
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Cmsbox\Cmcic\Model\Service\FormHandlerService $formHandler,
        \Cmsbox\Cmcic\Gateway\Config\Config $config,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->formHandler = $formHandler;
        $this->config = $config;
        $this->assetRepository = $assetRepository;

        // Prepare the field values
        $this->months = $this->formHandler->getMonths();
        $this->years = $this->formHandler->getYears();
    }
}

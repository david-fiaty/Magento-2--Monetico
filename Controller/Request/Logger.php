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

namespace Cmsbox\Cmcic\Controller\Request;

use Cmsbox\Cmcic\Gateway\Processor\Connector;

class Logger extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * Normal constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Cmsbox\Cmcic\Helper\Watchdog $watchdog
    ) {
        parent::__construct($context);

        $this->jsonFactory = $jsonFactory;
        $this->watchdog    = $watchdog;
    }
 
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            // Get the request data
            $logData = $this->getRequest()->getParam('log_data');

            // Log the data
            $this->watchdog->bark(Connector::KEY_REQUEST, $logData, $canDisplay = false, $canLog = true);
        }

        return $this->jsonFactory->create()->setData(
            [
            $this->handleError(__('Invalid AJAX request in logger controller.'))
            ]
        );
    }

    private function handleError($errorMessage)
    {
        $this->watchdog->logError($errorMessage);
        return $errorMessage;
    }
}

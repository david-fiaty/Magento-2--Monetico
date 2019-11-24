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

namespace Naxero\Monetico\Controller\Request;

use Naxero\Monetico\Gateway\Processor\Connector;

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
        \Naxero\Monetico\Helper\Watchdog $watchdog
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

            $response = true;
        }
        else {
            $response = $this->handleError(__('Invalid AJAX request in logger controller.'));
        }

        return $this->jsonFactory->create()->setData(
            [$response]
        );
    }

    private function handleError($errorMessage)
    {
        $this->watchdog->logError($errorMessage);
        return $errorMessage;
    }
}

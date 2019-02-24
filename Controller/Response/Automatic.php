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

namespace Cmsbox\Monetico\Controller\Response;
 
use Cmsbox\Monetico\Gateway\Processor\Connector;
use Cmsbox\Monetico\Gateway\Config\Core;

class Automatic extends \Magento\Framework\App\Action\Action
{
    /**
     * @var OrderHandlerService
     */
    protected $orderHandler;

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Watchdog
     */
    protected $watchdog;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * Automatic constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Cmsbox\Monetico\Model\Service\OrderHandlerService $orderHandler,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cmsbox\Monetico\Helper\Watchdog $watchdog,
        \Cmsbox\Monetico\Gateway\Config\Config $config,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader
    ) {
        parent::__construct($context);
        
        $this->orderHandler        = $orderHandler;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->watchdog            = $watchdog;
        $this->config              = $config;
        $this->moduleDirReader     = $moduleDirReader;
    }
 
    public function execute()
    {
        // Get the request data
        $responseData = $this->getRequest()->getPostValue();

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info(print_r($responseData ,1));

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = false);

        // Load the method instance
        $methodId = Core::moduleId() . '_' . Connector::KEY_REDIRECT_METHOD;
        $methodInstance = $this->methodHandler->getStaticInstance($methodId);

        // Process the response
        if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
            if ($methodInstance::isValidResponse($this->config, $methodId, $responseData, $this->moduleDirReader)) {
                if ($methodInstance::isSuccessResponse($this->config, $methodId, $responseData)) {
                    // Place order
                    $order = $this->orderHandler->placeOrder($responseData, $methodId);
                }
            }
        }

        // Stop the execution
        return $this->resultJsonFactory->create()->setData(
            [
            $this->handleError(__('Invalid request in automatic controller.'))
            ]
        );
    }

    private function handleError($errorMessage)
    {
        $this->watchdog->logError($errorMessage);
        return $errorMessage;
    }
}

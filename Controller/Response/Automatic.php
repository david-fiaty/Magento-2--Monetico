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

namespace Cmsbox\Cmcic\Controller\Response;
 
use Cmsbox\Cmcic\Gateway\Processor\Connector;
use Cmsbox\Cmcic\Gateway\Config\Core;

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
     * Automatic constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Cmsbox\Cmcic\Model\Service\OrderHandlerService $orderHandler,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Cmsbox\Cmcic\Helper\Watchdog $watchdog,
        \Cmsbox\Cmcic\Gateway\Config\Config $config
    ) {
        parent::__construct($context);
        
        $this->orderHandler        = $orderHandler;
        $this->resultJsonFactory   = $resultJsonFactory;
        $this->watchdog            = $watchdog;
        $this->config              = $config;
    }
 
    public function execute()
    {
        // Get the request data
        $responseData = $this->getRequest()->getPostValue();

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = false);

        // Load the method instance
        $methodId = Core::moduleId() . '_' . Connector::KEY_REDIRECT_METHOD;
        $methodInstance = $this->methodHandler->getStaticInstance($methodId);

        // Process the response
        if ($methodInstance && $methodInstance::isFrontend($this->config, $methodId)) {
            if ($methodInstance::isValidResponse($this->config, $methodId, $responseData)) {
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

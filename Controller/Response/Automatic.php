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

namespace Naxero\Monetico\Controller\Response;
 
use Naxero\Monetico\Gateway\Processor\Connector;
use Naxero\Monetico\Gateway\Config\Core;

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
    protected $resultRawFactory;

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
     * @var MethodHandlerService
     */
    public $methodHandler;

    /**
     * Automatic constructor.
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Naxero\Monetico\Model\Service\OrderHandlerService $orderHandler,
        \Magento\Framework\Controller\Result\Raw $resultRawFactory,
        \Naxero\Monetico\Helper\Watchdog $watchdog,
        \Naxero\Monetico\Gateway\Config\Config $config,
        \Magento\Framework\Module\Dir\Reader $moduleDirReader,
        \Naxero\Monetico\Model\Service\MethodHandlerService $methodHandler
    ) {
        parent::__construct($context);
        
        $this->orderHandler        = $orderHandler;
        $this->resultRawFactory   = $resultRawFactory;
        $this->watchdog            = $watchdog;
        $this->config              = $config;
        $this->moduleDirReader     = $moduleDirReader;
        $this->methodHandler       = $methodHandler;
    }
 
    public function execute()
    {
        // Get the request data
        $responseData = $this->getRequest()->getParams();

        // Log the response
        $this->watchdog->bark(Connector::KEY_RESPONSE, $responseData, $canDisplay = false);

        // Load the method instance
        $methodId = Core::moduleId() . '_' . Connector::KEY_REDIRECT_METHOD;
        $methodInstance = $this->methodHandler::getStaticInstance($methodId);

        if ($methodInstance) {
            // Get the response
            $response = $methodInstance::processResponse(
                $this->config,
                $methodId,
                $responseData,
                $this->moduleDirReader
            );
            
            // Process the response
            if (isset($response['isValid']) && $response['isValid'] === true) {
                if (isset($response['isSuccess']) && $response['isSuccess'] === true) {
                    // Place order
                    $order = $this->orderHandler->placeOrder(
                        $responseData['texte-libre'],
                        $methodId
                    );
                }
            }

            // Return the receipt
            return $this->resultRawFactory
            ->setHeader('Content-Type','text/plain')
            ->setContents($response['receipt']);
        }

        // Stop the execution
        return $this->resultRawFactory->create()->setData(
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

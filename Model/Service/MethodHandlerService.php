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

namespace Naxero\Monetico\Model\Service;

use Naxero\Monetico\Gateway\Config\Core;

class MethodHandlerService
{
    /**
     * @var Reader
     */
    protected $moduleDirReader;

    /**
     * MethodHandlerService constructor.
     */
    public function __construct(
        \Magento\Framework\Module\Dir\Reader $moduleDirReader
    ) {
        $this->moduleDirReader = $moduleDirReader;
    }

    private function getFiles($path)
    {
        $result = [];
        $flags = \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::UNIX_PATHS;
        $iterator = new \FilesystemIterator($path, $flags);
 
        foreach ($iterator as $file) {
            $fileName = $file->getFilename();
            if (strpos($fileName, '.') !== 0) {
                $name = basename($fileName, '.php');
                $result[$name] = $name;
            }
        }

        return $result;
    }

    /**
     * Build a payment method instance.
     */
    public static function getStaticInstance($methodId)
    {
        $classPath = "\\" . str_replace('_', "\\", Core::moduleName())
        . "\\Model\\Methods\\" . Core::methodName($methodId);
        if (class_exists($classPath)) {
            return $classPath;
        }

        return false;
    }

    private function getPath()
    {
        return $this->moduleDirReader->getModuleDir('', Core::moduleName()) . '/Model/Methods';
    }
}

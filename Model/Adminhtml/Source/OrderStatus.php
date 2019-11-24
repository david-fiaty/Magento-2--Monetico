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

namespace Naxero\Monetico\Model\Adminhtml\Source;

use Magento\Sales\Model\ResourceModel\Order\Status\Collection;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * @var Collection
     */
    protected $orderStatusCollection;

    /**
     * OrderStatus constructor.
     *
     * @param Collection $statusCollection
     */
    public function __construct(Collection $orderStatusCollection)
    {
        $this->orderStatusCollection = $orderStatusCollection;
    }

    /**
     * Return the order status options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->getStatusOptions();
    }

    /**
     * Get the order status options
     *
     * @return array
     */
    public function getStatusOptions()
    {
        // Return the options as array
        return $this->orderStatusCollection->toOptionArray();
    }
}

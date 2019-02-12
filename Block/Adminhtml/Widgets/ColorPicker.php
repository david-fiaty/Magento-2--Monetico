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

namespace Cmsbox\Cmcic\Block\Adminhtml\Widgets;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Cmsbox\Cmcic\Gateway\Config\Core;

class ColorPicker extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
     * @var Registry
     */
    protected $coreRegistry;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $cpPath = $this->getViewFileUrl(Core::moduleName() . '::js');

        // Build the javascript
        if (!$this->coreRegistry->registry('colorpicker_loaded')) {
            $html .= '<script type="text/javascript" src="' . $cpPath.'/'.'jscolor.js"></script>';
            $this->coreRegistry->registry('colorpicker_loaded', 1);
        }

        // Build the HTML
        $html .= '<script type="text/javascript">
                var el = document.getElementById("' . $element->getHtmlId() . '");
                el.className = el.className + " jscolor{hash:true}";
            </script>';
            
        return $html;
    }
}

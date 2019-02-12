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

define(
    [
        'uiComponent',
        'Cmsbox_Cmcic/js/view/payment/adapter',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        Adapter,
        RendererList
    ) {
        'use strict';

        // Get the config provider data
        var config = Adapter.getPaymentConfig();

        // Render the relevant payment methods
        for (var methodId in config) {
            if (config[methodId].active == 1) {
                // Prepare the js file name
                var parts = methodId.split('_');

                // Add it to the renderer list
                RendererList.push(
                    {
                        type: methodId,
                        component: config.module_name + '/js/view/payment/method-renderer/' + parts[2] + '_' + parts[3]
                    }
                );
            }
        }

        return Component.extend({});
    }
);

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

/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Cmsbox_Cmcic/js/view/payment/adapter',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/translate'
    ],
    function ($, Component, Adapter, FullScreenLoader, AdditionalValidators, t) {
        'use strict';

        window.checkoutConfig.reloadOnBillingAddress = true;
        var code = 'redirect_method';

        return Component.extend(
            {
                defaults: {
                    template: Adapter.getName() + '/payment/' + code + '.phtml',
                    moduleId: Adapter.getCode(),
                    methodId: Adapter.getMethodId(code),
                    config: Adapter.getPaymentConfig()[Adapter.getMethodId(code)],
                    targetButton:  Adapter.getMethodId(code) + '_button',
                    targetForm:  Adapter.getMethodId(code) + '_form',
                    redirectAfterPlaceOrder: false
                },

                /**
                 * @returns {exports}
                 */
                initialize: function () {
                    this._super();
                    this.data = {'method': this.methodId};
                    Adapter.log(this.config.request_data.params);
                },

                initObservable: function () {
                    this._super().observe([]);
                    return this;
                },

                /**
                 * @returns {string}
                 */
                getCode: function () {
                    return this.methodId;
                },

                /**
                 * @returns {bool}
                 */
                isActive: function () {
                    return this.config.active;
                },

                /**
                 * @returns {string}
                 */
                proceedWithSubmission: function () {
                    // Disable jQuery validate checks
                    $('#' + this.targetForm).validate().cancelSubmit = true;

                    // Submit the form
                    $('#' + this.targetForm).submit();
                },

                /**
                 * @returns {string}
                 */
                beforePlaceOrder: function () {
                    // Start the loader
                    FullScreenLoader.startLoader();

                    // Validate before submission
                    if (AdditionalValidators.validate()) {
                        // Set the cookie data
                        Adapter.setCookieData(this.methodId);
                    
                        // Log the request data
                        Adapter.backendLog(this.config.request_data.params);

                        // Submit
                        this.proceedWithSubmission();
                    } else {
                        FullScreenLoader.stopLoader();
                    }
                }
            }
        );
    }
);

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

/*browser:true*/
/*global define*/

define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Cmsbox_Monetico/js/view/payment/adapter',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/translate'
    ],
    function ($, Component, Adapter, FullScreenLoader, AdditionalValidators, t) {
        'use strict';

        window.checkoutConfig.reloadOnBillingAddress = true;
        var code = 'iframe_method';

        return Component.extend(
            {
                defaults: {
                    template: Adapter.getName() + '/payment/' + code + '.phtml',
                    moduleId: Adapter.getCode(),
                    methodId: Adapter.getMethodId(code),
                    config: Adapter.getPaymentConfig()[Adapter.getMethodId(code)],
                    targetButton:  Adapter.getMethodId(code) + '_button',
                    targetForm:  Adapter.getMethodId(code) + '_form',
                    targetIframe: '#targetIframe',
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
                createIframe: function () {
                    // Load the iframe
                    var targetIframe = $(this.targetIframe).contents().find('html');
                    $('#' + this.targetForm).detach().appendTo(targetIframe);
                },

                /**
                 * @returns {void}
                 */
                proceedWithSubmission: function () {
                    var targetIframe = $(this.targetIframe).contents().find('html');
                    targetIframe.find('form').submit();
                    $(this.targetIframe).css('display', 'block');
                    FullScreenLoader.stopLoader();
                },

                /**
                 * @returns {void}
                 */
                hideControls: function () {
                    // Hide billing, agreement and place order info
                    $('.checkout-billing-address').css('display', 'none');
                    $('.actions-toolbar').css('display', 'none');
                    $('.checkout-agreements-block').css('display', 'none');
                },

                /**
                 * @returns {string}
                 */
                beforePlaceOrder: function () {
                    // Start the loader
                    FullScreenLoader.startLoader();

                    // Validate before submission
                    if (AdditionalValidators.validate()) {
                        // Hide the controls
                        this.hideControls();

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

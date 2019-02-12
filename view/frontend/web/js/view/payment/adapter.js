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
    'jquery',
    'Magento_Ui/js/model/messageList',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'mage/url',
    'mage/cookies'
    ],
    function ($, GlobalMessageList, Quote, CheckoutData, Url) {
        'use strict';

        return {

            /**
             * Get payment configuration array.
             *
             * @returns {Array}
             */
            getPaymentConfig: function () {
                return window.checkoutConfig.payment['cmsbox_cmcic'];
            },
        
            /**
             * Get payment code.
             *
             * @returns {String}
             */
            getCode: function () {
                return this.getPaymentConfig()['module_id'];
            },

            /**
             * Get payment name.
             *
             * @returns {String}
             */
            getName: function () {
                return this.getPaymentConfig()['module_name'];
            },

            /**
             * Get payment method id.
             *
             * @returns {string}
             */
            getMethodId: function (methodId) {
                return this.getCode() + '_' + methodId;
            },

            /**
             * @returns {string}
             */
            getEmailAddress: function () {
                return window.checkoutConfig.customerData.email || Quote.guestEmail || CheckoutData.getValidatedEmailValue();
            },

            /**
             * @returns {void}
             */
            setCookieData: function (methodId) {
                // Set the email
                $.cookie(
                    this.getPaymentConfig()['email_cookie_name'],
                    this.getEmailAddress()
                );

                // Set the payment method
                $.cookie(
                    this.getPaymentConfig()['method_cookie_name'],
                    methodId
                );
            },

            /**
             * @returns {string}
             */
            getQuoteValue: function () {
                return (Quote.getTotals()().grand_total * 100).toFixed(2);
            },

            /**
             * Show error message
             */
            showMessage: function (type, message) {
                this.clearMessages();
                var messageContainer = $('.message');
                messageContainer.addClass('message-' + type + ' ' + type);
                messageContainer.append('<div>' + message + '</div>');
                messageContainer.show();
            },

            /**
             * Clear messages
             */
            clearMessages: function () {
                var messageContainer = $('.message');
                messageContainer.hide();
                messageContainer.empty();
            },

            /**
             * Log data to the browser console
             */
            log: function (data) {
                var isDebugMode = JSON.parse(this.getPaymentConfig(this.getCode())['debug']);
                var output = this.getCode() + ':' + JSON.stringify(data);
                if (isDebugMode) {
                    console.log(output);
                }
            },

            /**
             * Send data to back end for logging
             */
            backendLog: function (data) {
                var self = this;
                var isLoggingMode = JSON.parse(self.getPaymentConfig(self.getCode())['logging']);
                if (isLoggingMode) {
                    $.ajax(
                        {
                            type: "POST",
                            url: Url.build(self.getCode() + '/request/logger'),
                            data: {log_data: data},
                            error: function (request, status, error) {
                                self.log(error);
                            }
                        }
                    );
                }
            }
        };
    }
);

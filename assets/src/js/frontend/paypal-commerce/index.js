/* globals Give, jQuery, givePayPalCommerce, paypal */
import DonationForm from './DonationForm';
import SmartButtons from './SmartButtons';
import AdvancedCardFields from './AdvancedCardFields';
import CustomCardFields from './CustomCardFields';
import {loadScript} from '@paypal/paypal-js';
import {__} from '@wordpress/i18n';

document.addEventListener('DOMContentLoaded', () => {
    let $formWraps = document.querySelectorAll('.give-form-wrap');
    const $tmpFormWraps = [];

    // Filter container who has donation form.
    $formWraps.forEach($formWrap => {
        if (Give.form.fn.hasDonationForm($formWrap)) {
            $tmpFormWraps.push($formWrap);
        }
    });

    if (!$tmpFormWraps.length) {
        return;
    }

    $formWraps = $tmpFormWraps;

    // Setup initial PayPal Script on basis of first form on webpage.
    loadPayPalSDKScriptForDonationForm($formWraps[0].querySelector('.give-form'));

    // Attach tracking events to all forms except those displays in modal.
    $formWraps.forEach($formWrap => {
        const $form = $formWrap.querySelector('.give-form');

        // If form displays in model do not setup payment methods.
        // Payment methods will be setup on button click, after form loads in model.
        if( $formWrap.classList.contains('give-display-button-only') || $formWrap.classList.contains('give-display-modal') ) {
           return;
        }

        trackDonorActivityToRerenderPaymentMethods($form);
    });

    // Attach tracking events to forms displays in modal.
    jQuery('.give-form-wrap.give-display-button-only button.give-btn-modal, .give-form-wrap.give-display-modal button.give-btn-modal')
        .on('click', function () {

            // Wait for modal to open and form to load in modal.
            const timer = window.setInterval(() => {
                const $form = document.querySelector('.mfp-content form.give-form');

                if (! $form) {
                   return;
                }

                // Setup payment methods if PayPal Donations is selected.
                if (DonationForm.isPayPalCommerceSelected(jQuery($form))) {
                    setupPaymentMethod($form);
                }

                trackDonorActivityToRerenderPaymentMethods($form);

                window.clearInterval(timer);
            }, 100);
        });

    // On form submit prevent submission for PayPal commerce.
    // Form submission will be take care internally by smart buttons or advanced card fields.
    jQuery('form.give-form').on('submit', e => {
        if (!DonationForm.isPayPalCommerceSelected(jQuery(this))) {
            return true;
        }

        e.preventDefault();

        return false;
    });

    /**
     * Track donor activity to rerender payment methods.
     *
     * @since 3.5.0
     * @param {Element} $form Form selector.
     */
    function trackDonorActivityToRerenderPaymentMethods($form){
        setRecurringFieldTrackerToReloadPaypalSDK($form);
        setFormCurrencyTrackerToReloadPaypalSDK($form);
        setupGatewayLoadEventToRenderPaymentMethods($form);
    }

    /**
     * Setup recurring field tracker to reload paypal sdk.
     *
     * @since 2.9.0
     * @param {object} $form Form selector
     */
    function setRecurringFieldTrackerToReloadPaypalSDK($form) {
        const recurringField = $form.querySelector('input[name="_give_is_donation_recurring"]');

        if (recurringField) {
            DonationForm.trackRecurringHiddenFieldChange(recurringField, () => {
                loadPayPalSDKScriptForDonationForm($form);
            });
        }
    }

    /**
     * Setup gateway load event to render payment methods.
     *
     * @since 2.9.0
     * @param {object} $form Form selector
     */
    function setupGatewayLoadEventToRenderPaymentMethods($form) {
        document.addEventListener('give_gateway_loaded', () => {
            if (!DonationForm.isPayPalCommerceSelected(jQuery($form))) {
                return;
            }

            loadPayPalSDKScriptForDonationForm($form);
        });
    }

    /**
     * Setup form currency tracker to reload paypal sdk.
     *
     * @since 2.9.0
     * @param {object} $form Form selector
     */
    function setFormCurrencyTrackerToReloadPaypalSDK($form) {
        DonationForm.trackDonationCurrencyChange($form, () => {
            loadPayPalSDKScriptForDonationForm($form);
        });
    }

    /**
     * Setup PayPal payment methods
     *
     * @since 2.9.0
     */
    function setupPaymentMethods() {
        const setup = ($form) => {
            if (!DonationForm.isPayPalCommerceSelected(jQuery($form))) {
                return;
            }

            setupPaymentMethod($form);
        };

        // Setup payment methods for all forms, except those displays in modal.
        $formWraps.forEach($formWrap => {
            const $form = $formWrap.querySelector('.give-form');

            // If form displays in model do not setup payment methods.
            // Payment methods will be setup on button click, after form loads in model.
            if( $formWrap.classList.contains('give-display-button-only') || $formWrap.classList.contains('give-display-modal') ) {
                return;
            }

            if( $form ) {
                setup($form);
            }
        });

        // Setup payment methods for form displays in modal.
        const $donationFormInModel = document.querySelector('.mfp-content form.give-form');
        if ($donationFormInModel) {
            setup($donationFormInModel);
        }
    }

    /**
     * Setup payment  method.
     *
     * @since 2.9.0
     *
     * @param {object} $form Form selector
     */
    function setupPaymentMethod($form) {
        const smartButtons = new SmartButtons($form);
        const customCardFields = new CustomCardFields($form);

        if (SmartButtons.canShow()) {
            smartButtons.boot();
        }

        // Boot CustomCardFields class before AdvancedCardFields because of internal dependencies.
        if (AdvancedCardFields.canShow()) {
            const advancedCardFields = new AdvancedCardFields(customCardFields);

            customCardFields.boot();
            advancedCardFields.boot();

            return;
        }

        customCardFields.removeFields();
    }

    /**
     * Load PayPal script.
     *
     * @param {object} form Form selector
     *
     * @since 2.9.0
     *
     * @return {Promise}  PayPal sdk load promise.
     */
    async function loadPayPalScript(form) {
        const options = {...givePayPalCommerce.payPalSdkQueryParameters};
        const isRecurring = DonationForm.isRecurringDonation(form);

        options.intent = isRecurring ? 'subscription' : 'capture';
        options.vault = !!isRecurring;
        options.currency = Give.form.fn.getInfo('currency_code', jQuery(form));

        return await loadScript(options)
    }

    /**
     * @since 2.33.0 Add logic to reload PayPal SDK script for donation form.
     * @since 2.20.0
     * @param {object} $form
     */
    function loadPayPalSDKScriptForDonationForm($form) {
        loadPayPalScript($form)
            .then(() => {setupPaymentMethods();})
            .then(() => {
                // Check if hosted fields are not available but enabled in admin settings.
                let payPalComponents = givePayPalCommerce.payPalSdkQueryParameters.components.split(',');

                // Check if hosted fields are enabled in admin settings.
                // Do not need to reload PayPal SDK if hosted fields are not enabled.
                const isHostedFieldsEnabled = paypal.hasOwnProperty('HostedFields')
                    && payPalComponents.indexOf('hosted-fields') !== -1;
                if(!isHostedFieldsEnabled) {
                    return;
                }

                // Reload PayPal SDK if hosted fields are not available.
                // This will enable Credit and Debit card smart button.
                if( !AdvancedCardFields.canShow()  ) {
                    // Reset PayPal components to reload hosted fields.
                    payPalComponents = payPalComponents.filter(component => component !== 'hosted-fields');
                    givePayPalCommerce.payPalSdkQueryParameters.components = payPalComponents.join(',');

                    // Load PayPal script again.
                    loadPayPalSDKScriptForDonationForm($form);
                }
            })
            .catch((e) => {
                const jQueryForm = jQuery($form);
                Give.form.fn.addErrors(
                    jQueryForm,
                    Give.form.fn.getErrorHTML([{
                        message: __('A problem has occurred with the connection between this site and PayPal, preventing donations. Please contact site administrators if reloading the page does not fix the issue. This is usually the result of some JavaScript conflict on the page.', 'give')
                    }])
                );

                Give.form.fn.disable(jQueryForm, true);
                console.error(e);
            });
    }
});

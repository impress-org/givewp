import h from 'vhtml';
import accounting from 'accounting';
import {domIsReady, insertAfter, nodeFromString, removeNode} from './not-jquery.js';

// Transforms document for classic template
domIsReady(() => {
    removeTestModeMessage();
    movePersonalInfoSectionAfterDonationAmountSection();
    movePaymentFormInsidePaymentDetailsSection();
    moveDonateNowButtonSectionAfterDonationAmountSection();
    setPersonalInfoTitle();
    addPersonalInfoDescription();
    setPaymentDetailsTitle();
    addPaymentDetailsDescription();
    splitDonationLevelAmountsIntoParts();
    moveDefaultGatewayDataIntoActiveGatewaySection();
    splitGatewayResponse();
});

/**
 * Individual transformations
 */

function removeTestModeMessage() {
    removeNode(document.querySelector('#give_error_test_mode')); // Get out of my way!
}

function movePersonalInfoSectionAfterDonationAmountSection() {
    insertAfter(
        document.querySelector('.give-personal-info-section'),
        document.querySelector('.give-donation-amount-section')
    );
}

function moveDonateNowButtonSectionAfterDonationAmountSection() {
    insertAfter(
        document.querySelector('.give-donate-now-button-section'),
        document.querySelector('.give-payment-details-section')
    );
}

function setPersonalInfoTitle() {
    document.querySelector('.give-personal-info-section legend:first-of-type').textContent =
        classicTemplateOptions.donor_information.headline;
}

function addPersonalInfoDescription() {
    insertAfter(
        nodeFromString(
            h('p', {className: 'give-personal-info-description'}, classicTemplateOptions.donor_information.description)
        ),
        document.querySelector('.give-personal-info-section legend:first-of-type')
    );
}

function setPaymentDetailsTitle() {
    document.querySelector('.give-payment-mode-label').textContent = classicTemplateOptions.payment_method.headline;
}

function addPaymentDetailsDescription() {
    insertAfter(
        nodeFromString(h('p', {className: 'give-payment-details-description'})),
        document.querySelector('.give-payment-mode-label')
    );
}

function movePaymentFormInsidePaymentDetailsSection() {
    document.querySelector('.give-payment-details-section').append(document.querySelector('#give_purchase_form_wrap'));
}

function splitDonationLevelAmountsIntoParts() {
    const currency = {
        code: window.Give.fn.getGlobalVar('currency'),
        decimalSeparator: window.Give.fn.getGlobalVar('decimal_separator'),
        precision: Number.parseInt(window.Give.fn.getGlobalVar('number_decimals')),
        symbol: window.Give.fn.getGlobalVar('currency_sign'),
        symbolPosition: window.Give.fn.getGlobalVar('currency_pos'),
        thousandsSeparator: window.Give.fn.getGlobalVar('thousands_separator'),
    };

    document.querySelectorAll('.give-donation-level-btn:not(.give-btn-level-custom)').forEach((node) => {
        const rawAmount = window.Give.fn.unFormatCurrency(node.getAttribute('value'), currency.decimalSeparator);
        const amountWithoutDecimal = accounting.format(rawAmount, 0, currency.thousandsSeparator);
        const decimalForAmount = rawAmount.toFixed(currency.precision).split('.')[1];

        // Use the formatted amount as the ARIA label.
        node.setAttribute('aria-label', node.textContent);

        const CurrencySymbol = ({position}) =>
            h('span', {className: `give-currency-symbol-${position}`}, currency.symbol);

        // This is a visual representation of the amount. The decimal separator
        // omitted since it is not displayed. The ARIA label includes the
        // properly formatted amount, so we hide the contents for screen
        // readers.
        node.innerHTML = h(
            'span',
            {
                className: 'give-formatted-currency',
                'aria-hidden': true,
            },
            currency.symbolPosition === 'before' && h(CurrencySymbol, {position: 'before'}),
            h(
                'span',
                {className: 'give-amount-formatted'},
                h('span', {className: 'give-amount-without-decimals'}, amountWithoutDecimal),
                h('span', {className: 'give-amount-decimal'}, decimalForAmount)
            ),
            currency.symbolPosition === 'after' && h(CurrencySymbol, {position: 'after'})
        );
    });
}

function moveDefaultGatewayDataIntoActiveGatewaySection() {
    addSelectedGatewayDetails(
        createGatewayDetails(
            document.querySelector('#give_purchase_form_wrap fieldset:not(.give-donation-submit)').innerHTML
        )
    );
}

function splitGatewayResponse() {
    jQuery.ajaxPrefilter(function (options, originalOptions) {
        if (options.url.includes('?payment-mode=')) {
            // Override beforeSend callback
            options.beforeSend = function () {
                jQuery('.give-donate-now-button-section').block({
                    message: null,
                    overlayCSS: {
                        background: '#fff',
                        opacity: 0.6,
                    },
                });

                // Remove previous gateway data
                removeNode(document.querySelector('.give-gateway-details'));

                if (originalOptions.beforeSend instanceof Function) {
                    originalOptions.beforeSend();
                }
            };
            // Override the success callback
            options.success = function (responseHTML) {
                // Trigger original success callback
                originalOptions.success(responseHTML);

                removeNode(document.querySelector('#give_purchase_form_wrap'));

                const gatewayDetails = createGatewayDetails(responseHTML);

                // The following both removes the sections from gatewayDetails,
                // but transplants their content to sections in the form.

                // Transplant the existing personal info content with the markup from the gateway’s HTML
                document
                    .querySelector('.give-personal-info-section')
                    .replaceChildren(
                        ...gatewayDetails.removeChild(gatewayDetails.querySelector('.give-personal-info-section'))
                            .children
                    );
                setPersonalInfoTitle();
                addPersonalInfoDescription();

                // Replace the donation button section with the markup from the gateway’s HTML
                document
                    .querySelector('.give-donate-now-button-section')
                    .replaceWith(
                        ...gatewayDetails.removeChild(gatewayDetails.querySelector('#give_purchase_submit')).children
                    );

                // Add the gateway details to the form
                addSelectedGatewayDetails(gatewayDetails);

                jQuery('.give-donate-now-button-section').unblock();
            };
        }
    });
}

const createGatewayDetails = (html) => nodeFromString(`<div class="give-gateway-details">${html}</div>`);

const addSelectedGatewayDetails = (gatewayDetailsNode) =>
    document.querySelector('.give-gateway-option-selected > .give-gateway-option').after(gatewayDetailsNode);

window.GiveClassicTemplate = {
    share: (element) => {
        let url = parent.window.location.toString();
        if (window.Give.fn.getParameterByName('giveDonationAction', url)) {
            url = window.Give.fn.removeURLParameter(url, 'giveDonationAction');
            url = window.Give.fn.removeURLParameter(url, 'payment-confirmation');
            url = window.Give.fn.removeURLParameter(url, 'payment-id');
        }

        if (element.classList.contains('facebook-btn')) {
            window.Give.share.fn.facebook(url);
        } else if (element.classList.contains('twitter-btn')) {
            window.Give.share.fn.twitter(url, classicTemplateOptions.donation_receipt.twitter_message);
        }

        return false;
    },
};

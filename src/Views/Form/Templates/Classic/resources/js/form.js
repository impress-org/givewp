import h from 'vhtml';
import {
    domIsReady,
    insertAfter,
    measureText,
    nodeFromString,
    pixelsToEm,
    pixelsToRem,
    removeNode,
} from './not-jquery.js';

import {
    IS_CURRENCY_SWITCHING_ACTIVE,
    IS_DONATION_SUMMARY_ACTIVE,
    IS_RECURRING_ACTIVE,
    IS_STRIPE_ACTIVE,
} from './is-feature-active.js';

// This must be called ASAP (since this is used when DOMContentLoaded happens)
// It does not use anything inside the body.
IS_STRIPE_ACTIVE && setStripeElementStyles();

// Transforms document for classic template
domIsReady(() => {
    /* TODO: don’t load this script for the receipt in the first place */
    if (document.getElementById('give-receipt')) return;

    setContainerMode();
    movePersonalInfoSectionAfterDonationAmountSection();
    movePaymentFormInsidePaymentDetailsSection();
    moveDonateNowButtonSectionAfterDonationAmountSection();
    setDonationAmountSectionDescription();
    setPersonalInfoTitle();
    addPersonalInfoDescription();
    setPaymentDetailsTitle();
    addPaymentDetailsDescription();
    setupDonationLevels();
    moveDefaultGatewayDataIntoActiveGatewaySection();
    IS_DONATION_SUMMARY_ACTIVE && moveDonationSummaryAfterDonationAmountSection();
    IS_RECURRING_ACTIVE && attachRecurringDonationEvents();
    splitGatewayResponse();
    IS_CURRENCY_SWITCHING_ACTIVE && setupCurrencySwitcherSelector();
    IS_RECURRING_ACTIVE && setRecurringPeriodSelectWidth();
    addSecurePaymentBadgeToDonateNowSection();
    moveTestModeMessage();
    IS_CURRENCY_SWITCHING_ACTIVE && moveCurrencySwitcherMessageOutsideOfWrapper();
    addFancyBorderWhenChecked();
});

/**
 * Individual transformations
 */

function setContainerMode() {
    document.body.classList.add(`give-container-${window.classicTemplateOptions.visual_appearance.container_style}`);
}

function moveTestModeMessage() {
    const testModeMessage = document.querySelector('#give_error_test_mode');

    if (testModeMessage) {
        if (hasSingleGateway()) {
            document.querySelector('#give_secure_site_wrapper').before(testModeMessage);
        } else {
            document.querySelector('.give-payment-mode-label').after(testModeMessage);
        }
    }
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

function moveDonationSummaryAfterDonationAmountSection() {
    const donationSummary = document.querySelector('.give-donation-form-summary-section');
    const paymentDetails = document.querySelector('.give-payment-details-section');

    if (donationSummary.closest('.give-donate-now-button-section')) {
        // Move when inside give-donate-now-button-section
        insertAfter(donationSummary, paymentDetails);
    } else if (donationSummary.closest('.give-personal-info-section')) {
        // Move to before gateway section inside give-personal-info-section
        paymentDetails.parentNode.insertBefore(donationSummary, paymentDetails);
    }
}

function setPersonalInfoTitle() {
    if (classicTemplateOptions.donor_information.headline) {
        document.querySelector('.give-personal-info-section legend:first-of-type').textContent =
            classicTemplateOptions.donor_information.headline;
    }
}

function addPersonalInfoDescription() {
    if (classicTemplateOptions.donor_information.description) {
        insertAfter(
            nodeFromString(
                h(
                    'p',
                    {className: 'give-personal-info-description'},
                    classicTemplateOptions.donor_information.description
                )
            ),
            document.querySelector('#give_checkout_user_info legend:first-of-type')
        );
    }
}

function setPaymentDetailsTitle() {
    if (classicTemplateOptions.payment_information.headline) {
        document.querySelector('.give-payment-mode-label').textContent =
            classicTemplateOptions.payment_information.headline;
    }
}

function addPaymentDetailsDescription() {
    if (classicTemplateOptions.payment_information.description) {
        insertAfter(
            nodeFromString(
                `<p class="give-payment-mode-description">${classicTemplateOptions.payment_information.description}</p>`
            ),
            document.querySelector('.give-payment-mode-label')
        );
    }
}

function movePaymentFormInsidePaymentDetailsSection() {
    document.querySelector('.give-payment-details-section').append(document.querySelector('#give_purchase_form_wrap'));
}

function setupDonationLevels() {
    // If currency switching is available, we need to watch and re-run the setup
    // for the donation levels. Otherwise, we can just run the setup once with
    // the global currency settings.
    if (IS_CURRENCY_SWITCHING_ACTIVE) {
        // This window object has the supported currencies and their symbols.
        const supportedCurrencies = JSON.parse(window.give_cs_json_obj).supported_currency;

        const selectedCurrencyInput = document.querySelector('input[name=give-cs-form-currency]');

        // For selected currency value changes, re-run the donation level setup
        // with the new currency symbol.
        const selectedCurrencyObserver = new MutationObserver(([selectedCurrencyMutation]) => {
            const currencyCode = selectedCurrencyMutation.target.value;

            const selectedCurrencyConfig = supportedCurrencies[currencyCode];
            splitDonationLevelAmountsIntoParts({
                symbol: selectedCurrencyConfig.symbol,
                decimalSeparator: selectedCurrencyConfig.setting.decimal_separator,
                precision: selectedCurrencyConfig.setting.number_decimals,
            });
        });

        // Run the donation level setup with the selected currency.
        const selectedCurrencyConfig = supportedCurrencies[selectedCurrencyInput.value];
        splitDonationLevelAmountsIntoParts({
            symbol: selectedCurrencyConfig.symbol,
            decimalSeparator: selectedCurrencyConfig.setting.decimal_separator,
            precision: selectedCurrencyConfig.setting.number_decimals,
        });

        // Start observing the selected currency input.
        selectedCurrencyObserver.observe(selectedCurrencyInput, {attributeFilter: ['value']});
    } else splitDonationLevelAmountsIntoParts({});
}

function splitDonationLevelAmountsIntoParts({
    symbol = window.Give.fn.getGlobalVar('currency_sign'),
    symbolPosition = window.Give.fn.getGlobalVar('currency_pos'),
    //thousandsSeparator = window.Give.fn.getGlobalVar('thousands_separator'),
    decimalSeparator = window.Give.fn.getGlobalVar('decimal_separator'),
    //precision = Number.parseInt(window.Give.fn.getGlobalVar('number_decimals')),
}) {
    document.querySelectorAll('.give-donation-level-btn:not(.give-btn-level-custom)').forEach((node) => {
        // if the button has custom text display it as a tooltip
        if (node.innerHTML !== (symbolPosition === 'before' ? symbol + node.value : node.value + symbol)) {
            addTooltipToLevel(node);
        }

        const amount = node.getAttribute('value');
        const [amountWithoutDecimal, decimalForAmount] = amount.split(decimalSeparator);

        // Use the formatted amount as the ARIA label for node and tooltip.
        const amountWithSymbol = symbolPosition === 'before' ? `${symbol}${amount}` : `${amount}${symbol}`;
        if (node.parentNode && node.parentNode.getAttribute('aria-label') == node.getAttribute('aria-label')) {
            node.parentNode.setAttribute('aria-label', amountWithSymbol);
        }
        node.setAttribute('aria-label', amountWithSymbol);

        const CurrencySymbol = ({position}) => h('span', {className: `give-currency-symbol-${position}`}, symbol);

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
            symbolPosition === 'before' && h(CurrencySymbol, {position: 'before'}),
            h(
                'span',
                {className: 'give-amount-formatted'},
                h('span', {className: 'give-amount-without-decimals'}, amountWithoutDecimal),
                h('span', {className: 'give-amount-decimal'}, decimalForAmount)
            ),
            symbolPosition === 'after' && h(CurrencySymbol, {position: 'after'})
        );
    });
}

function addTooltipToLevel(node) {
    const parent = node.parentNode;
    if (!node.getAttribute('has-tooltip')) {
        const tooltip = nodeFromString(
            h('span', {
                className: 'give-tooltip hint--top hint--bounce',
                'aria-label': parent.getAttribute('aria-label'),
            })
        );
        if (node.innerHTML.length < 50) {
            tooltip.classList.add('narrow');
        }
        parent.replaceChild(tooltip, node);
        tooltip.appendChild(node);
        node.setAttribute('has-tooltip', 'true');
    }
}

function moveDefaultGatewayDataIntoActiveGatewaySection() {
    if (hasSingleGateway()) {
        return;
    }

    addSelectedGatewayDetails(createGatewayDetails());

    const purchaseFormWrap = document.querySelector('#give_purchase_form_wrap');
    purchaseFormWrap.removeChild(purchaseFormWrap.querySelector('.give-donation-submit'));
    document.querySelector('.give-gateway-details').append(...purchaseFormWrap.children);

    removeNode(purchaseFormWrap);
}

function attachRecurringDonationEvents() {
    const recurringPeriod = document.querySelector('[name="give-recurring-period"]');

    if (recurringPeriod) {
        recurringPeriod.addEventListener('change', function (e) {
            window.GiveDonationSummary.handleDonorsChoiceRecurringFrequency(e.target, jQuery('.give-form'));
        });

        document.querySelector('.give-recurring-donors-choice-period')?.addEventListener('change', function () {
            window.GiveDonationSummary.handleDonorsChoiceRecurringFrequency(recurringPeriod, jQuery('.give-form'));
        });

        // Admin choice
        document.querySelector('[name="give-price-id"]')?.addEventListener('change', function (e) {
            window.GiveDonationSummary.handleAdminDefinedRecurringFrequency(e.target, jQuery('.give-form'));
        });
    }
}

function updateRecurringDonationFrequency() {
    const form = jQuery('.give-form');
    const donorChoice = document.querySelector('[name="give-recurring-period"]');
    const adminChoice = document.querySelector('[name="give-price-id"]');

    if (donorChoice) {
        window.GiveDonationSummary.handleDonorsChoiceRecurringFrequency(donorChoice, form);
    }

    if (adminChoice) {
        window.GiveDonationSummary.handleAdminDefinedRecurringFrequency(adminChoice, form);
    }
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

                const gatewayDetails = createGatewayDetails();
                gatewayDetails.innerHTML = responseHTML;

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

                addSecurePaymentBadgeToDonateNowSection();

                // Donation Summary
                if (IS_DONATION_SUMMARY_ACTIVE) {
                    document
                        .querySelector('.give-donation-form-summary-section')
                        .replaceChildren(
                            ...gatewayDetails.removeChild(
                                gatewayDetails.querySelector('.give-donation-form-summary-section')
                            ).children
                        );

                    window.GiveDonationSummary.initTotal();
                }

                // Remove previous gateway data (just in case it was added again by multiple clicks)
                removeNode(document.querySelector('.give-gateway-details'));

                // Add the gateway details to the form
                addSelectedGatewayDetails(gatewayDetails);

                // Recurring Donations
                if (IS_RECURRING_ACTIVE) {
                    updateRecurringDonationFrequency();
                }

                jQuery('.give-donate-now-button-section').unblock();
            };
        }
    });
}

const createGatewayDetails = () => nodeFromString(`<div class="give-gateway-details"></div>`);

const addSelectedGatewayDetails = (gatewayDetailsNode) =>
    jQuery('.give-gateway-option-selected > .give-gateway-option').after(gatewayDetailsNode);

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

function setupCurrencySwitcherSelector() {
    window.Give_Currency_Switcher.adjust_dropdown_width = () => {
        const currencySelect = document.querySelector('.give-cs-select-currency');
        const currencyText = document.querySelector('.give-currency-symbol');
        currencySelect.style.setProperty('--currency-text-width', pixelsToRem(measureText(currencyText)));
        currencySelect.style.width = null;
    };

    window.Give_Currency_Switcher.adjust_dropdown_width();
}

function setRecurringPeriodSelectWidth() {
    const select = document.querySelector('.give-recurring-donors-choice-period');

    if (select) {
        function updateWidth() {
            select.style.setProperty('--selected-text-width', pixelsToEm(measureText(select, 'value'), select));
        }

        // Update after the fonts load.
        // Note: FontFaceSet’s loadingdone does not seem to work in Safari.
        document.fonts.ready.then(updateWidth);

        // Update when the value changes.
        select.addEventListener('change', updateWidth);
    }
}

function addSecurePaymentBadgeToDonateNowSection() {
    if (window.classicTemplateOptions.visual_appearance.secure_badge === 'enabled') {
        document
            .querySelector('.give-donate-now-button-section')
            .lastChild.after(
                nodeFromString(
                    h(
                        'aside',
                        {className: 'give-secure-donation-badge-bottom'},
                        h('svg', {className: 'give-form-secure-icon'}, h('use', {href: '#give-icon-lock'})),
                        window.classicTemplateOptions.visual_appearance.secure_badge_text
                    )
                )
            );
    }
}

function setDonationAmountSectionDescription() {
    if (classicTemplateOptions.donation_amount.description) {
        document
            .querySelector('.give-amount-heading')
            .after(
                nodeFromString(
                    h('p', {className: 'give-amount-description'}, classicTemplateOptions.donation_amount.description)
                )
            );
    }
}

function moveCurrencySwitcherMessageOutsideOfWrapper() {
    const currencySwitcherMessage = document.querySelector('.give-currency-switcher-msg-wrap');
    currencySwitcherMessage.parentNode.after(currencySwitcherMessage);
}

function addFancyBorderWhenChecked() {
    const nodes = document.querySelectorAll(`.give-donation-amount-section input[type="checkbox"]`);

    nodes.forEach((node) =>
        node.addEventListener('change', (event) => {
            event.target.parentNode.classList.toggle('checked-within');
        })
    );
}

function setStripeElementStyles() {
    window.give_stripe_vars.element_font_styles = {
        cssSrc: document.querySelector('#give-google-font-css')?.href,
    };

    Object.assign(window.give_stripe_vars.element_base_styles, {
        color: '#828382',
        fontFamily: window.getComputedStyle(document.body).fontFamily,
        fontWeight: 400,
    });
}

function hasSingleGateway() {
    return document.getElementById('give-gateway-radio-list').children.length === 1;
}

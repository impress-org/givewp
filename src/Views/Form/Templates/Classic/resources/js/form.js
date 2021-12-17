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
    IS_FEE_RECOVERY_ACTIVE,
    IS_RECURRING_ACTIVE,
} from './is-feature-active.js';

// Transforms document for classic template
domIsReady(() => {
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
    IS_FEE_RECOVERY_ACTIVE && attachFeeEvents() && updateFeesAmount();
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
    document.querySelector('.give-payment-mode-label').after(document.querySelector('#give_error_test_mode'));
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
    insertAfter(
        document.querySelector('.give-donation-form-summary-section'),
        document.querySelector('.give-payment-details-section')
    );

    updateDonationSummaryAmount();
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
    document.querySelector('.give-payment-mode-label').textContent =
        classicTemplateOptions.payment_information.headline;
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
        const amount = node.getAttribute('value');
        const [amountWithoutDecimal, decimalForAmount] = amount.split(decimalSeparator);

        // Use the formatted amount as the ARIA label.
        node.setAttribute('aria-label', symbolPosition === 'before' ? `${symbol}${amount}` : `${amount}${symbol}`);

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

function moveDefaultGatewayDataIntoActiveGatewaySection() {
    addSelectedGatewayDetails(
        createGatewayDetails(
            Array.from(document.querySelectorAll('#give_purchase_form_wrap fieldset:not(.give-donation-submit)'))
                .map((node) => node.outerHTML)
                .join('')
        )
    );

    removeNode(document.querySelector('#give_purchase_form_wrap'));
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

function updateDonationSummaryAmount() {
    document.querySelector('[data-tag="amount"]').innerHTML = document.querySelector('#give-amount').value;
}

function attachFeeEvents() {
    const coverFeesCheckbox = document.querySelector('.give_fee_mode_checkbox');

    if (coverFeesCheckbox) {
        coverFeesCheckbox.addEventListener('change', updateFeesAmount);
        new MutationObserver(updateFeesAmount).observe(document.querySelector('.give-fee-message-label-text'), {
            childList: true,
        });
    } else {
        jQuery('.js-give-donation-summary-fees').hide();
    }
}

function updateFeesAmount() {
    window.GiveDonationSummary.handleFees(document.querySelector('.give_fee_mode_checkbox'), jQuery('.give-form'));
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
                    updateDonationSummaryAmount();
                    IS_FEE_RECOVERY_ACTIVE && updateFeesAmount();
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
        // Note: FontFaceSet’s loadingdone doesn’t seem to work in Safari.
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
    document
        .querySelector('.give-amount-heading')
        .after(
            nodeFromString(
                h('p', {className: 'give-amount-description'}, classicTemplateOptions.donation_amount.description)
            )
        );
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

import accounting from 'accounting';

/**
 * @since 2.17.0
 */
window.GiveDonationSummary = {
    init: function () {
        GiveDonationSummary.initAmount();
        GiveDonationSummary.initFrequency();
        GiveDonationSummary.initFees();
        GiveDonationSummary.initTotal();
    },

    /**
     * @since 2.17.0
     */
    initAmount: function () {
        GiveDonationSummary.observe('[name="give-amount"]', function (targetNode, $form) {
            $form.find('[data-tag="amount"]').html(GiveDonationSummary.format_amount(targetNode.value, $form));
        });
    },

    /**
     * @since 2.17.0
     */
    initFrequency: function () {
        // Donor's Choice Recurring
        GiveDonationSummary.observe('[name="give-recurring-period"]', function (targetNode, $form) {
            $form.find('.js-give-donation-summary-frequency-help-text').toggle(!targetNode.checked);
            $form.find('[data-tag="frequency"]').toggle(!targetNode.checked);
            $form.find('[data-tag="recurring"]').toggle(targetNode.checked).html(targetNode.dataset['periodLabel']);

            // Donor's Choice Period
            const donorsChoice = document.querySelector('[name="give-recurring-period-donors-choice"]');
            if (donorsChoice) {
                const donorsChoiceValue = donorsChoice.options[donorsChoice.selectedIndex].value || false;
                if (donorsChoiceValue) {
                    $form
                        .find('[data-tag="recurring"]')
                        .html(GiveDonationSummaryData.recurringLabelLookup[donorsChoiceValue]);
                }
            }
        });

        // Admin Defined Recurring
        GiveDonationSummary.observe('[name="give-price-id"]', function (targetNode, $form) {
            const priceID = targetNode.value;
            const recurringDetailsEl = document.querySelector('.give_recurring_donation_details');

            if (!recurringDetailsEl) {
                return;
            }

            const recurringDetails = JSON.parse(recurringDetailsEl.value);

            if ('undefined' !== typeof recurringDetails['multi']) {
                const isRecurring = 'yes' === recurringDetails['multi'][priceID]['_give_recurring'];
                const periodLabel = recurringDetails['multi'][priceID]['give_recurring_pretty_text'];

                $form.find('.js-give-donation-summary-frequency-help-text').toggle(!isRecurring);
                $form.find('[data-tag="frequency"]').toggle(!isRecurring);
                $form.find('[data-tag="recurring"]').toggle(isRecurring).html(periodLabel);
            }
        });
    },

    /**
     * @since 2.17.0
     */
    initFees: function () {
        GiveDonationSummary.observe('.give_fee_mode_checkbox', function (targetNode, $form) {
            $form.find('.fee-break-down-message').hide();
            $form.find('.js-give-donation-summary-fees').toggle(targetNode.checked);

            // Hack: (Currency Switcher) The total is always stored using a the decimal separator as set by the primary currency.
            const fee = document
                .querySelector('[name="give-fee-amount"]')
                .value.replace('.', Give.form.fn.getInfo('decimal_separator', $form));
            $form.find('[data-tag="fees"]').html(GiveDonationSummary.format_amount(fee, $form));
        });
    },

    /**
     * @since 2.17.0
     */
    initTotal: function () {
        GiveDonationSummary.observe('.give-final-total-amount', function (targetNode, $form) {
            // Hack: (Currency Switcher) The total is always stored using a the decimal seperator as set by the primary currency.
            const total = targetNode.dataset.total.replace('.', Give.form.fn.getInfo('decimal_separator', $form));
            $form.find('[data-tag="total"]').html(GiveDonationSummary.format_amount(total, $form));
        });

        // Hack: Force an initial mutation for the Total Amount observer
        const totalAmount = document.querySelector('.give-final-total-amount');
        if (totalAmount) {
            totalAmount.dataset.total = totalAmount.dataset.total;
        }
    },

    /**
     * Hack: Placeholder callback, which is only used when the gateway changes.
     */
    handleNavigateBack: function () {},

    /**
     * Hack: Changing gateways re-renders parts of the form via AJAX.
     */
    onGatewayLoadSuccess: function () {
        const inserted = jQuery('#give_purchase_form_wrap .give-donation-summary-section').detach();
        if (inserted.length) {
            jQuery('.give-donation-summary-section').remove();
            inserted.appendTo('#donate-fieldset');
            GiveDonationSummary.initTotal();

            // Overwrite the handler because updating the gateway breaks the original binding.
            GiveDonationSummary.handleNavigateBack = function (e) {
                e.stopPropagation();
                e.preventDefault();
                window.formNavigator.back();
            };
        }
    },

    /**
     * Observe an element and respond to changes to that element.
     *
     * @since 2.17.0
     *
     * @param {string} selectors
     * @param {callable} callback
     */
    observe: function (selectors, callback) {
        const targetNode = document.querySelector(selectors);

        if (!targetNode) return;

        const $form = jQuery(targetNode.closest('.give-form'));

        new MutationObserver(function (mutationsList, observer) {
            for (const mutation of mutationsList) {
                // Use traditional 'for loops' for IE 11
                if (mutation.type === 'attributes') {
                    /**
                     * @param targetNode The node matching the element as defined by the specific selectors
                     * @param $form The closest `.give-form` node to the targetNode, wrapped in jQuery
                     */
                    callback(targetNode, $form);
                }
            }
        }).observe(targetNode, {attributes: true});
    },

    /**
     * Helper function to get the formatted amount
     *
     * @since 2.17.0
     *
     * @param {string/number} amount
     * @param {jQuery} $form
     */
    format_amount: function (amount, $form) {
        // Normalize amounts to JS number format
        amount = amount
            .replace(Give.form.fn.getInfo('thousands_separator', $form), '')
            .replace(Give.form.fn.getInfo('decimal_separator', $form), '.');

        const currency = Give.form.fn.getInfo('currency_code', $form);
        const precision = GiveDonationSummaryData.currencyPrecisionLookup[currency];

        // Format with accounting.js, according to the configuration
        return accounting.formatMoney(amount, {
            symbol: Give.form.fn.getInfo('currency_symbol', $form),
            format: 'before' === Give.form.fn.getInfo('currency_position', $form) ? '%s%v' : '%v%s',
            decimal: Give.form.fn.getInfo('decimal_separator', $form),
            thousand: Give.form.fn.getInfo('thousands_separator', $form),
            precision: precision,
        });
    },
};

jQuery(document).on('give:postInit', GiveDonationSummary.init);
jQuery(document).on('Give:onGatewayLoadSuccess', GiveDonationSummary.onGatewayLoadSuccess);

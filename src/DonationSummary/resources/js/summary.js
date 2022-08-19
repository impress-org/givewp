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
     * Recurring frequency
     *
     * @since 2.17.0
     */
    initFrequency: function () {
        // Donor's Choice Recurring
        GiveDonationSummary.observe(
            '[name="give-recurring-period"]',
            GiveDonationSummary.handleDonorsChoiceRecurringFrequency
        );

        // Admin Defined Recurring
        GiveDonationSummary.observe('[name="give-price-id"]', GiveDonationSummary.handleAdminDefinedRecurringFrequency);

        // Admin Defined Recurring - "Set Donation"
        GiveDonationSummary.observe(
            '[name="_give_is_donation_recurring"]',
            GiveDonationSummary.handleAdminDefinedSetDonationFrequency
        );

        // Admin Defined Recurring - "Multi-level"
        GiveDonationSummary.observe('[name="give-price-id"]', GiveDonationSummary.handleAdminDefinedRecurringFrequency);
    },

    /**
     * @since 2.18.0
     */
    handleDonorsChoiceRecurringFrequency: function (targetNode, $form) {
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
    },

    /**
     * @since 2.18.0
     */
    handleAdminDefinedRecurringFrequency: function (targetNode, $form) {
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
    },

    handleAdminDefinedSetDonationFrequency: function (targetNode, $form) {
        const isRecurring = targetNode.value;
        const adminChoice = document.querySelector('.give-recurring-admin-choice');

        if (isRecurring && adminChoice) {
            $form.find('.js-give-donation-summary-frequency-help-text').toggle(!isRecurring);
            $form.find('[data-tag="frequency"]').toggle(!isRecurring);
            $form.find('[data-tag="recurring"]').html(adminChoice.textContent);
        }
    },

    /**
     * @since 2.17.0
     */
    initFees: function () {
        GiveDonationSummary.observe('.give_fee_mode_checkbox', GiveDonationSummary.handleFees);
        const checkbox = document.querySelector('.give_fee_mode_checkbox');
        if (!checkbox) {
            GiveDonationSummary.observe('.give-fee-message-label-text', GiveDonationSummary.handleFees);
        }
    },

    /**
     * @since 2.18.0
     */
    handleFees: function (targetNode, $form) {
        $form.find('.fee-break-down-message').hide();
        $form.find('.js-give-donation-summary-fees').toggle(targetNode.checked);

        if (!targetNode.checked) {
            return;
        }

        const formData = new FormData($form[0]);
        const fee = formData.get('give-fee-amount');
        $form.find('[data-tag="fees"]').html(GiveDonationSummary.format_amount(fee, $form, false));
    },

    /**
     * @since 2.17.0
     */
    initTotal: function () {
        GiveDonationSummary.observe('.give-final-total-amount', function (targetNode, $form) {
            const total = targetNode.dataset.total;
            $form.find('[data-tag="total"]').html(GiveDonationSummary.format_amount(total, $form, false));
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
     * @param {boolean} callImmediately
     */
    observe: function (selectors, callback, callImmediately = true) {
        const targetNode = document.querySelector(selectors);

        if (!targetNode) return;

        const $form = jQuery(targetNode.closest('.give-form'));

        new MutationObserver(function (mutationsList) {
            for (const mutation of mutationsList) {
                // Use traditional 'for loops' for IE 11
                if (mutation.type === 'attributes') {
                    /**
                     * @param targetNode The node matching the element as defined by the specific selectors
                     * @param $form The closest `.give-form` node to the targetNode, wrapped in jQuery
                     */
                    callback(mutation.target, $form);
                }
            }
        }).observe(targetNode, {attributes: true});

        if (callImmediately) {
            callback(targetNode, $form);
        }
    },

    /**
     * Helper function to get the formatted amount
     *
     * @since 2.17.0
     *
     * @param {string/number} amount
     * @param {jQuery} $form
     * @param {boolean} normalize
     */
    format_amount: function (amount, $form, normalize = true) {
        // Normalize amounts to JS number format
        if (normalize) {
            amount = Give.fn.unFormatCurrency(amount, Give.form.fn.getInfo('decimal_separator', $form));
        }

        const fee_recovery_enabled = document.querySelector('.give-fee-message-label-text');
        format_args = {
            symbol: Give.form.fn.getInfo('currency_symbol', $form),
            precision: fee_recovery_enabled ? 2 : Give.form.fn.getInfo('number_decimals', $form),
        };

        // Format with accounting.js, according to the configuration
        return Give.fn.formatCurrency(amount, format_args, $form);
    },
};

jQuery(document).on('give:postInit', GiveDonationSummary.init);
jQuery(document).on('Give:onGatewayLoadSuccess', GiveDonationSummary.onGatewayLoadSuccess);

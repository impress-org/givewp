/**
 * @since 2.17.0
 */
window.GiveDonationSummary = {
    init: function () {
        GiveDonationSummary.initAmount();
        GiveDonationSummary.initFrequency();
        GiveDonationSummary.initTotal();
    },

    /**
     * This function returns formated donation amount.
     *
     * @since 2.23.1
     * @return {string}
     */
    getFormattedDonationAmount: function ($form) {
        const unFormattedAmount = Give.fn.unFormatCurrency(
            $form.find('[name="give-amount"]').val(),
            Give.form.fn.getInfo('decimal_separator', $form)
        );

        return Give.fn.formatCurrency(
            unFormattedAmount,
            {symbol: Give.form.fn.getInfo('currency_symbol', $form)},
            $form
        );
    },

    /**
     * @since 2.17.0
     *
     * @since 2.24.0 add eventlistner for custom input amount changes.
     */
    initAmount: function () {
        GiveDonationSummary.observe('[name="give-amount"]', function (targetNode, $form) {
            $form.find('[data-tag="amount"]').html(GiveDonationSummary.getFormattedDonationAmount($form));
        });

        const targetNode = document.querySelector('[name="give-amount"]');
        if (targetNode) {
            const $form = jQuery(targetNode.closest('.give-form'));

            targetNode.addEventListener('change', function () {
                $form.find('[data-tag="amount"]').html(GiveDonationSummary.getFormattedDonationAmount($form));
            });
        }
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
     * @since 2.23.1 Remove dependency on checkbox. Removed first argument.
     * @since 2.18.0
     */
    handleFees: function ($form) {
        const feeModeEnableElement = $form.find('[name="give-fee-mode-enable"]');

        if (!feeModeEnableElement || 'true' !== $form.find('[name="give-fee-mode-enable"]').val()) {
            $form.find('.js-give-donation-summary-fees').toggle(false);
            return;
        }

        $form.find('.js-give-donation-summary-fees').toggle(true);

        const feeMessageTemplateParts = $form.find('.give-fee-message-label').attr('data-feemessage').split(' ');
        const feeMessageParts = $form.find('.give-fee-message-label-text').text().split(' ');
        const formattedFeeAmount = feeMessageParts
            .filter((messagePart) => !feeMessageTemplateParts.includes(messagePart))
            .pop();
        $form.find('[data-tag="fees"]').html(formattedFeeAmount);
    },

    /**
     * @since 2.17.0
     */
    initTotal: function () {
        GiveDonationSummary.observe('.give-final-total-amount', function (targetNode, $form) {
            $form.find('[data-tag="total"]').html(targetNode.textContent);

            GiveDonationSummary.handleFees($form);
        });

        // Force an initial mutation for the Total Amount observer
        const totalAmount = document.querySelector('.give-final-total-amount');
        if (totalAmount) {
            totalAmount.textContent = totalAmount.textContent;
        }
    },

    /**
     * Placeholder callback, which is only used when the gateway changes.
     */
    handleNavigateBack: function () {},

    /**
     * Changing gateways re-renders parts of the form via AJAX.
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
};

jQuery(document).on('give:postInit', GiveDonationSummary.init);
jQuery(document).on('Give:onGatewayLoadSuccess', GiveDonationSummary.onGatewayLoadSuccess);

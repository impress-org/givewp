jQuery(document).on('give:postInit', function() {

    /**
     * Amount
     * @unreleased
     */
    GiveDonationSummary.observe( '[name="give-amount"]', function( targetNode, $form ) {
        $form.find( '[data-tag="amount"]' ).html(
            GiveDonationSummary.format_amount( targetNode.value, $form )
        )
    })

    /**
     * Frequency (and Recurring)
     * @unreleased
     */
    GiveDonationSummary.observe( '[name="give-recurring-period"]', function( targetNode, $form ) {
        $form.find( '.js-give-donation-summary-frequency-help-text' ).toggle( ! targetNode.checked )
        $form.find( '[data-tag="frequency"]' ).toggle( ! targetNode.checked)
        $form.find( '[data-tag="recurring"]' ).toggle( targetNode.checked ).html( targetNode.dataset['periodLabel'] )
    })

    /**
     * Fees
     * @unreleased
     */
    GiveDonationSummary.observe('.give_fee_mode_checkbox', function (targetNode, $form) {
        $form.find('.fee-break-down-message').hide()
        $form.find('.js-give-donation-summary-fees').toggle(targetNode.checked)

        // Hack: (Currency Switcher) The total is always stored using a the decimal seperator as set by the primary currency.
        const fee = document.querySelector('[name="give-fee-amount"]').value.replace('.', Give.form.fn.getInfo( 'decimal_separator', $form ))
        $form.find('[data-tag="fees"]').html(
            GiveDonationSummary.format_amount(fee, $form)
        )
    })

    /**
     * Total
     * @unreleased
     */
    GiveDonationSummary.observe( '.give-final-total-amount', function( targetNode, $form ) {
        // Hack: (Currency Switcher) The total is always stored using a the decimal seperator as set by the primary currency.
        const total = targetNode.dataset.total.replace('.', Give.form.fn.getInfo( 'decimal_separator', $form ))
        $form.find( '[data-tag="total"]' ).html(
            GiveDonationSummary.format_amount( total, $form )
        )
    })

    // Hack: Force an initial mutation for the Total Amount observer
    const totalAmount = document.querySelector('.give-final-total-amount')
    totalAmount.dataset.total = totalAmount.dataset.total
})

/**
 * @unreleased
 */
const GiveDonationSummary = {

    /**
     * Observe an element and respond to changes to that element.
     *
     * @unreleased
     *
     * @param {string} selectors
     * @param {callable} callback
     */
    observe: function( selectors, callback ) {
        const targetNode = document.querySelector( selectors )
        const $form = jQuery( targetNode.closest('.give-form') )

        if( ! targetNode ) return;

        new MutationObserver(function(mutationsList, observer) {
            for(const mutation of mutationsList) { // Use traditional 'for loops' for IE 11
                if (mutation.type === 'attributes') {
                    /**
                     * @param targetNode The node matching the element as defined by the specific selectors
                     * @param $form The closest `.give-form` node to the targetNode, wrapped in jQuery
                     */
                    callback( targetNode, $form )
                }
            }
        }).observe(targetNode, { attributes: true });
    },

    /**
     * Helper function to get the formatted amount
     *
     * @unreleased
     *
     * @param {string/number} amount
     * @param {jQuery} $form
     */
    format_amount: function( amount, $form ) {

        // Normalize amounts to JS number format
        amount = amount.replace( Give.form.fn.getInfo( 'thousands_separator', $form ), '' )
                       .replace( Give.form.fn.getInfo( 'decimal_separator', $form ), '.')

        const currency = Give.form.fn.getInfo( 'currency_code', $form )
        const precision = GiveDonationSummaryData.currencyPrecisionLookup[ currency ]

        // Format with accounting.js, according to the configuration
        return accounting.formatMoney( amount, {
            symbol: Give.form.fn.getInfo( 'currency_symbol', $form ),
            format: ( 'before' === Give.form.fn.getInfo( 'currency_position', $form ) ) ? '%s%v' : '%v%s',
            decimal: Give.form.fn.getInfo( 'decimal_separator', $form ),
            thousand: Give.form.fn.getInfo( 'thousands_separator', $form ),
            precision: precision,
        } )
    }
}

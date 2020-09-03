/* globals Give, Promise  */
class DonationForm {
	/**
	 * Get form Data.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} $form Form selector.
	 *
	 * @return {FormData} FormData object of form entries.
	 */
	static getFormDataWithoutGiveActionField( $form ) {
		const formData = new FormData( $form ); // eslint-disable-line

		formData.delete( 'give_action' );

		return formData;
	}

	/**
	 * Add PayPal order id as hidden type input field to donation form.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} $form Form selector.
	 * @param {string} orderId PayPal order id.
	 *
	 * @return {Promise} Promise of appending hidden input field to donation form.
	 */
	static attachOrderIdToForm( $form, orderId ) {
		const input = document.createElement( 'input' );

		input.type = 'hidden';
		input.name = 'payPalOrderId';
		input.value = orderId;

		return new Promise( ( resolve, reject ) => { // eslint-disable-line
			resolve( $form.appendChild( input ) );
		} );
	}

	/**
	 * Check if donor selected PayPal Commerce payment gateway or not.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} $form Form jQuery object.
	 *
	 * @return {boolean} Return whether or not donor selected PayPal Commerce payment gateway.
	 */
	static isPayPalCommerceSelected( $form ) {
		return 'paypal-commerce' === Give.form.fn.getGateway( $form );
	}

	/**
	 * Add error notices to donation form.
	 * Note: this function will add error at beginning of credit card fields section.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} $form Jquery Form object
	 * @param {string} errors Error list HTML.
	 */
	static addErrors( $form, errors ) {
		$form.find( '#give-paypal-commerce-smart-buttons-wrap' ).before( errors );
	}

	/**
	 * Return whether or not current donation is recurring.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} form Form Selector.
	 *
	 * @return {boolean}  Return whether or not donor opted in for subscription.
	 */
	static isRecurringDonation( form ) {
		// Recurring choice field will be not available if donation form set to "Admin Defined" Recurring Donations.
		// In that case we can still find type of donation by checking "_give_is_donation_recurring" field value.
		const recurringChoiceHiddenField = form.querySelector( 'input[name="_give_is_donation_recurring"]' );

		return recurringChoiceHiddenField && '1' === recurringChoiceHiddenField.value;
	}

	/**
	 * Call function when change field attribute.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} element Javascript selector
	 * @param {object} handler Function
	 */
	static trackRecurringHiddenFieldChange( element, handler ) {
		const MutationObserver = new window.MutationObserver( function( mutations ) {
			if ( 'value' === mutations[ 0 ].attributeName ) {
				handler.call();
			}
		} );

		MutationObserver.observe( element, {
			attributeFilter: [ 'value' ],
		} );
	}
}

export default DonationForm;

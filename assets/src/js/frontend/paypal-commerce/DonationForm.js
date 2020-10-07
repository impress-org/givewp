/* globals Give, Promise  */
import AdvancedCardFields from './AdvancedCardFields';
import CustomCardFields from './CustomCardFields';

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
	 * @param {string} fieldName Field name.
	 *
	 * @return {Promise} Promise of appending hidden input field to donation form.
	 */
	static addFieldToForm( $form, orderId, fieldName ) {
		const input = document.createElement( 'input' );

		input.type = 'hidden';
		input.name = fieldName;
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
			// Exit if value does not change.
			if ( mutations[ 0 ].oldValue === mutations[ 0 ].target.value ) {
				return;
			}

			// Exit if paypal-commerce is not selected.
			if ( ! DonationForm.isPayPalCommerceSelected( jQuery( mutations[ 0 ].target ).closest( '.give-form' ) ) ) {
				return;
			}

			handler.call();
		} );

		MutationObserver.observe( element, {
			attributeFilter: [ 'value' ],
			attributeOldValue: true,
		} );
	}

	/**
	 * Call function when change field attribute.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} element Javascript selector
	 * @param {object} handler Function
	 */
	static trackDonationCurrencyChange( element, handler ) {
		const MutationObserver = new window.MutationObserver( function( mutations ) {
			// Exit if data attribute does not change does not change.
			if ( mutations[ 0 ].oldValue === mutations[ 0 ].target.getAttribute( 'data-currency_code' ) ) {
				return;
			}

			// Exit if paypal-commerce is not selected.
			if ( ! DonationForm.isPayPalCommerceSelected( jQuery( mutations[ 0 ].target ) ) ) {
				return;
			}

			handler.call();
		} );

		MutationObserver.observe( element, {
			attributeFilter: [ 'data-currency_code' ],
			attributeOldValue: true,
		} );
	}

	/**
	 * Hide donate now button if only PayPal smart buttons payment method available.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} form form javascript selector.
	 */
	static toggleDonateNowButton( form ) {
		let display = '';

		// Hide the buttons if there are no custom credit card fields
		if ( ! AdvancedCardFields.canShow() || ( DonationForm.isRecurringDonation( form ) && ! CustomCardFields.canShow( form ) ) ) {
			display = 'none';
		}

		form.querySelector( 'input[name="give-purchase"]' ).style.display = display;
	}
}

export default DonationForm;

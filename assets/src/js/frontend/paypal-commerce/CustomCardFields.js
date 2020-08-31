import PaymentMethod from './PaymentMethod';
import DonationForm from './DonationForm';
import AdvancedCardFields from './AdvancedCardFields';

class CustomCardFields extends PaymentMethod {
	/**
	 * @inheritDoc
	 */
	constructor( form ) {
		super( form );

		this.payPalSupportedCountriesForCardSubscription = [ 'US', 'AU' ];
		this.cardFields = this.getCardFields();
		this.recurringChoiceField = this.form.querySelector( 'input[name="give-recurring-period"]' );

		if ( this.recurringChoiceField ) {
			this.recurringChoiceField.addEventListener( 'change', this.renderPaymentMethodOption.bind( this ) );
		}
	}

	/**
	 * @inheritDoc
	 */
	renderPaymentMethodOption() {
		// Show custom card field only if donor opted for recurring donation.
		// And PayPal account is from supported country.
		// We can not process recurring donation with advanced card fields, so let hide and use card field to process recurring donation with PayPal subscription api.
		this.toggleFields();
	}

	/**
	 * Get list of credit card fields.
	 *
	 * @since 2.9.0
	 *
	 * @return {object} object of card field selectors.
	 */
	getCardFields() {
		return {
			number: {
				el: this.form.querySelector( 'input[name="card_number"]' ),
			},
			cvv: {
				el: this.form.querySelector( 'input[name="card_cvc"]' ),
			},
			expirationDate: {
				el: this.form.querySelector( 'input[name="card_expiry"]' ),
			},
		};
	}

	/**
	 * Toggle fields.
	 *
	 * @since 2.9.0
	 */
	toggleFields() {
		const display = this.canShow() ? 'block' : 'none';

		for ( const type in this.cardFields ) {
			this.cardFields[ type ].el.style.display = display;
			this.cardFields[ type ].el.disabled = 'none' === display;
		}
	}

	/**
	 * Return whether or not custom card field available to process subscriptions.
	 *
	 * @since 2.9.0
	 *
	 * @return {boolean} Return whether or not display custom card fields.
	 */
	canShow() {
		return AdvancedCardFields.canShow() &&
			DonationForm.isRecurringDonation( this.form ) &&
			this.payPalSupportedCountriesForCardSubscription.includes( window.givePayPalCommerce.accountCountry );
	}
}

export default CustomCardFields;

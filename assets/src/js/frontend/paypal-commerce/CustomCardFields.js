import PaymentMethod from './PaymentMethod';
import DonationForm from './DonationForm';
import AdvancedCardFields from './AdvancedCardFields';

class CustomCardFields extends PaymentMethod {
	/**
	 * @inheritDoc
	 */
	constructor( form ) {
		super( form );

		this.setUpProperties();
	}

	/**
	 * Setup properties.
	 *
	 * @since 2.9.0
	 */
	setUpProperties() {
		this.payPalSupportedCountriesForCardSubscription = [ 'US', 'AU' ];
		this.cardFields = this.getCardFields();
		this.recurringChoiceField = this.form.querySelector( 'input[name="give-recurring-period"]' );
	}

	/**
	 * @inheritDoc
	 */
	registerEvents() {
		if ( this.recurringChoiceField ) {
			this.recurringChoiceField.addEventListener( 'change', this.renderPaymentMethodOption.bind( this ) );
		}

		this.separator = this.cardFields.number.el.parentElement.insertAdjacentElement( 'beforebegin', this.separatorHtml() );
	}

	/**
	 * @inheritDoc
	 */
	onGatewayLoadBoot( evt, response, formIdAttr ) {
		const self = evt.data.self;

		self.setUpProperties();
		self.registerEvents();

		super.onGatewayLoadBoot( evt, response, formIdAttr );
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

	/**
	 * Remove card fields.
	 *
	 * @since 2.9.0
	 */
	removeFields() {
		for ( const type in this.cardFields ) {
			this.cardFields[ type ].el.parentElement.remove();
		}

		this.form.querySelector( 'input[name="card_name"]' ).parentElement.remove();
		this.form.querySelector( '[id*="give_cc_fields-"] .separator-with-text' ).parentElement.remove();
	}

	/**
	 * Return separator html.
	 *
	 * @since 2.9.0
	 *
	 * @return {object} separator Node.
	 */
	separatorHtml() {
		const div = document.createElement( 'div' );

		div.setAttribute( 'class', 'separator-with-text' );
		div.innerHTML = `<div class="dashed-line"></div><div class="label">${ window.givePayPalCommerce.separatorLabel }</div><div class="dashed-line"></div>`;

		return div;
	}
}

export default CustomCardFields;

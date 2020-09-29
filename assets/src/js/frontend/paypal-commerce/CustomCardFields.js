/* globals givePayPalCommerce */
import PaymentMethod from './PaymentMethod';
import DonationForm from './DonationForm';
import AdvancedCardFields from './AdvancedCardFields';

class CustomCardFields extends PaymentMethod {
	/**
	 * @inheritDoc
	 */
	constructor( form ) {
		super( form );

		this.setupProperties();
	}

	/**
	 * Setup properties.
	 *
	 * @since 2.9.0
	 */
	setupProperties() {
		this.cardFields = this.getCardFields();
		this.recurringChoiceHiddenField = this.form.querySelector( 'input[name="_give_is_donation_recurring"]' );
		this.separator = this.cardFields.number.el.parentElement.insertAdjacentElement( 'beforebegin', this.separatorHtml() );
	}

	/**
	 * @inheritDoc
	 */
	registerEvents() {
		if ( this.recurringChoiceHiddenField ) {
			DonationForm.trackRecurringHiddenFieldChange( this.recurringChoiceHiddenField, this.renderPaymentMethodOption.bind( this ) );
		}
	}

	/**
	 * @inheritDoc
	 */
	onGatewayLoadBoot( evt, self ) {
		if ( self.isProcessingEventForForm( evt.detail.formIdAttribute ) ) {
			self.setupProperties();
		}

		super.onGatewayLoadBoot( evt, self );
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
		const display = CustomCardFields.canShow( this.form ) ? 'block' : 'none';

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
	 * @param {object} form Form javascript selector
	 *
	 * @return {boolean} Return whether or not display custom card fields.
	 */
	static canShow( form ) {
		return AdvancedCardFields.canShow() &&
			DonationForm.isRecurringDonation( form ) &&
			[ 'US', 'AU' ].includes( window.givePayPalCommerce.accountCountry );
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
		this.separator && this.separator.remove(); // eslint-disable-line
	}

	/**
	 * Remove custom card fields on gateway load.
	 *
	 * @since 2.9.0
	 */
	removeFieldsOnGatewayLoad() {
		const handler = evt => {
			if ( this.isProcessingEventForForm( evt.detail.formIdAttribute ) ) {
				this.setupProperties();
				this.removeFields.bind( this ).call();
			}
		};

		document.addEventListener( 'give_gateway_loaded', evt => handler( evt ) );
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
		div.innerHTML = `<div class="dashed-line"></div><div class="label">${ givePayPalCommerce.separatorLabel }</div><div class="dashed-line"></div>`;

		return div;
	}
}

export default CustomCardFields;

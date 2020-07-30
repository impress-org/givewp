/* globals paypal, Give */
import DonationForm from './DonationForm';
import PaymentMethod from './PaymentMethod';

class AdvancedCardFields extends PaymentMethod {
	/**
	 * Return whether or not render credit card fields.
	 *
	 * @since 2.8.0
	 *
	 * @return {boolean} Return boolean value whether we can render card fields or not.
	 */
	canRenderFields() {
		return paypal.HostedFields.isEligible() === true;
	}

	/**
	 * Render payment method.
	 *
	 * @since 2.8.0
	 */
	async renderPaymentMethodOption() {
		if ( ! this.canRenderFields() ) {
			return;
		}

		const creatOrderHandler = this.createOrderHandler.bind( this );
		const self = this;

		await paypal.HostedFields.render( {
			createOrder: creatOrderHandler,
			styles: {
				input: this.getComputedInputFieldStyle(),
			},
			fields: this.getFields(),
		} );

		this.jQueryForm.on( 'submit', event => {
			event.preventDefault();

			paypal.HostedFields.submit().then( payload => { // eslint-disable-line
				Give.form.fn.showProcessingState();

				const result = self.isPaymentApproved();

				if ( ! result ) {
					Give.form.fn.hideProcessingState();
					alert( 'Something went wrong' ); //eslint-disable-line
				}

				self.jQueryForm.submit();
			} );

			return false;
		} );
	}

	/**
	 * Create order event handler for smart buttons.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return PayPal order id.
	 */
	async createOrderHandler( data, actions ) { // eslint-disable-line
		// eslint-disable-next-line
		const response = await fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_create_order`, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} );

		const responseJson = await response.json();

		return responseJson.data.id;
	}

	/**
	 * Get fields.
	 *
	 * @since 2.8.0
	 * @return {object} Return object of card input field container details.
	 */
	getFields() {
		return {
			number: {
				selector: `#${ this.form.querySelector( 'div[id^="give-card-number-field-"]' ).getAttribute( 'id' ) }`,
				placeholder: 'Card Number',
			},
			cvv: {
				selector: `#${ this.form.querySelector( 'div[id^="give-card-cvc-field-"]' ).getAttribute( 'id' ) }`,
				placeholder: 'CVV',
			},
			expirationDate: {
				selector: `#${ this.form.querySelector( 'div[id^="give-card-expiration-field-"]' ).getAttribute( 'id' ) }`,
				placeholder: 'MM/YY',
			},
		};
	}

	/**
	 * Approve PayPal payment after successfully payment.
	 *
	 * @since 2.8.0
	 *
	 * @return {Promise<any>} Return request response.
	 */
	async approvePayment() {
		// eslint-disable-next-line
		const response = await fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_approve_order&order=` + payload.orderId, {
			method: 'POST',
		} );

		return await response.json();
	}

	/**
	 * Return wether or not payment approved successfully.
	 *
	 * @since 2.8.0
	 *
	 * @return {Promise<boolean>} Return boolean whether Payment approved or not.
	 */
	async isPaymentApproved() {
		const result = await this.approvePayment();

		return true === result.success;
	}

	/**
	 * Get computed style of credit card input field.
	 *
	 * @since 2.8.0
	 *
	 * @return {object} Return object of style properties.
	 */
	getComputedInputFieldStyle() {
		const computedStyle = window.getComputedStyle( this.form.querySelector( 'input[name="card_name"]' ), null );

		return {
			'font-size': computedStyle.getPropertyValue( 'font-size' ),
			'font-family': computedStyle.getPropertyValue( 'font-family' ),
			color: computedStyle.getPropertyValue( 'color' ),
		};
	}
}

export default AdvancedCardFields;

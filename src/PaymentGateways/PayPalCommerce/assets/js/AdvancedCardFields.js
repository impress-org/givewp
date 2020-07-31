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
		const onSubmitHandlerForDonationForm = this.onSubmitHandlerForDonationForm.bind( this );

		const hostedCardFields = await paypal.HostedFields.render( {
			createOrder: creatOrderHandler,
			styles: this.getComputedInputFieldStyle(),
			fields: this.getFields(),
		} );

		// @todo: add form submit event only if processing payment with advanced card fields.
		this.jQueryForm.on( 'submit', { hostedCardFields }, onSubmitHandlerForDonationForm );
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
	 * @param {string} orderId Order id.
	 *
	 * @return {Promise<any>} Return request response.
	 */
	async approvePayment( orderId ) {
		// eslint-disable-next-line
		const response = await fetch( `${ this.ajaxurl }?action=give_paypal_commerce_approve_order&order=` + orderId, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
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
			input: {
				'font-size': computedStyle.getPropertyValue( 'font-size' ),
				'font-family': computedStyle.getPropertyValue( 'font-family' ),
				color: computedStyle.getPropertyValue( 'color' ),
			},
		};
	}

	/**
	 *
	 * Handle donation form submit event.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} event jQuery event object.
	 *
	 * @return {boolean} Return boolean false value to stop form submission.
	 */
	async onSubmitHandlerForDonationForm( event ) {
		event.preventDefault();

		const payload = await event.data.hostedCardFields.submit().catch( error => {
			console.log( error.message ); //eslint-disable-line
		} );

		// Approve payment on if we did not get any error.
		if ( payload ) {
			await this.onApproveHandler( payload );
		}

		return false;
	}

	/**
	 * Handle PayPal payment on approve event.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} payload PayPal response object after payment completion.
	 */
	async onApproveHandler( payload ) {
		Give.form.fn.showProcessingState();

		const result = await this.approvePayment( payload.orderId );

		if ( ! result.success ) {
			Give.form.fn.hideProcessingState();

			if ( result.data.errorMsg ) {
				alert(result.data.errorMsg); //eslint-disable-line
			}
		}

		await DonationForm.attachOrderIdToForm( this.form, result.data.order.id );

		this.jQueryForm.off( 'submit' );
		this.jQueryForm.submit();
	}
}

export default AdvancedCardFields;

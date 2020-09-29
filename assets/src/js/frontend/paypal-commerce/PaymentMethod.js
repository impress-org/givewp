/* globals jQuery, Give, givePayPalCommerce */
import DonationForm from './DonationForm';

class PaymentMethod {
	/**
	 * Constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} form selector.
	 */
	constructor( form ) {
		this.form = form;
		this.jQueryForm = jQuery( form );
		this.ajaxurl = Give.fn.getGlobalVar( 'ajaxurl' );
	}

	/**
	 * Setup properties.
	 *
	 * @since 2.9.0
	 */
	setupProperties() {}

	/**
	 * Render PayPal smart buttons.
	 *
	 * @since 2.9.0
	 */
	boot() {
		const self = this;

		document.addEventListener( 'give_gateway_loaded', evt => {
			this.onGatewayLoadBoot( evt, self );
		} );

		if ( DonationForm.isPayPalCommerceSelected( this.jQueryForm ) ) {
			this.registerEvents();
			this.renderPaymentMethodOption();
		}
	}

	/**
	 * Render paypal buttons when reload payment gateways.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} evt Event object.
	 * @param {object} self Class object.
	 */
	onGatewayLoadBoot( evt, self ) {
		if ( evt.detail.formIdAttribute === self.form.getAttribute( 'id' ) && DonationForm.isPayPalCommerceSelected( self.jQueryForm ) ) {
			self.renderPaymentMethodOption();
		}
	}

	/**
	 * Render payment method.
	 *
	 * @since 2.9.0
	 */
	renderPaymentMethodOption() {}

	/**
	 * Register events.
	 *
	 * @since 2.9.0
	 */
	registerEvents() {}

	/**
	 * Show error on donation form.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} error PayPal error object
	 */
	showError( error = null ) {
		if ( null === error ) {
			DonationForm.addErrors( this.jQueryForm, Give.form.fn.getErrorHTML( [ { message: givePayPalCommerce.defaultDonationCreationError } ] ) );
			return;
		}

		const errorDetail = error.details[ 0 ];
		DonationForm.addErrors( this.jQueryForm, Give.form.fn.getErrorHTML( [ { message: errorDetail.description } ] ) );
	}

	/**
	 * Return whether or not process donation form same donation form.
	 *
	 * @since 2.9.0
	 *
	 * @param {string} formId Donation form id attribute value.
	 * @return {boolean|boolean} Return true if processing same form otherwise false.
	 */
	isProcessingEventForForm( formId ) {
		return formId === this.form.getAttribute( 'id' ) && DonationForm.isPayPalCommerceSelected( this.jQueryForm );
	}

	/**
	 * Create order event handler for smart buttons.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return PayPal order id.
	 */
	async createOrderHandler(data, actions) { // eslint-disable-line
		Give.form.fn.removeErrors( this.jQueryForm );

		// eslint-disable-next-line
		const response = await fetch(`${this.ajaxurl}?action=give_paypal_commerce_create_order`, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} );
		const responseJson = await response.json();

		if ( ! responseJson.success ) {
			this.showError( responseJson.data.error );
			return null;
		}

		return responseJson.data.id;
	}
}

export default PaymentMethod;

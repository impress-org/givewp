/* globals jQuery, Give */
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
	 * Create order event handler for smart buttons.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return PayPal order id.
	 */
	async createOrderHandler( data, actions ) { // eslint-disable-line
		Give.form.fn.removeErrors( this.jQueryForm );

		// eslint-disable-next-line
		const response = await fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_create_order`, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} );

		const responseJson = await response.json();

		if ( ! responseJson.success ) {
			if ( null === responseJson.data.error ) {
				throw {};
			}

			throw responseJson.data.error;
		}

		return responseJson.data.id;
	}

	/**
	 * Checks whether GiveWP is in test mode
	 *
	 * @since 2.9.0
	 *
	 * @returns {boolean} whether or not GiveWP is in test mode
	 */
	isInTestMode() {
		return window.give_global_vars.is_test_mode === '1';
	}

	/**
	 * Display an error message to the user
	 *
	 * @since 2.9.0
	 *
	 * @param {string} error Message to display
	 * @param {boolean} showToDonor Whether the message is safe to show donors
	 */
	displayErrorMessage( error, showToDonor ) {
		let errorToDisplay;

		if ( showToDonor || this.isInTestMode() ) {
			errorToDisplay = error;
		} else {
			errorToDisplay = window.give_global_vars.generic_error_message;
		}

		Give.form.fn.getErrorHTML( [ { message: errorToDisplay } ] );
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
}

export default PaymentMethod;

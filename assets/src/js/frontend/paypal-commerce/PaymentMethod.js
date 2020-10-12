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

		this.setupProperties();
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
		this.renderPaymentMethodOption();
	}

	/**
	 * Render payment method.
	 *
	 * @since 2.9.0
	 */
	renderPaymentMethodOption() {}

	/**
	 * Show error on donation form.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} error PayPal error object
	 */
	showError( error ) {
		if ( error.hasOwnProperty( 'details' ) ) {
			DonationForm.addErrors( this.jQueryForm, Give.form.fn.getErrorHTML( [ { message: error.details[ 0 ].description } ] ) );
			return;
		}

		DonationForm.addErrors( this.jQueryForm, error );
	}

	/**
	 * Checks whether GiveWP is in test mode
	 *
	 * @since 2.9.0
	 *
	 * @returns {boolean} whether or not GiveWP is in test mode
	 */
	isInTestMode() {
		return Give.fn.getGlobalVar( 'is_test_mode' ) === '1';
	}

	/**
	 * Display an error message to the user
	 *
	 * @since 2.9.0
	 *
	 * @param {string} error Message to display
	 * @param {boolean} showToDonor Whether the message is safe to show donors. default false
	 */
	displayErrorMessage( error, showToDonor = false ) {
		let errorToDisplay = Give.form.fn.getErrorHTML( [ { message: window.givePayPalCommerce.genericDonorErrorMessage } ] );

		if ( showToDonor || this.isInTestMode() ) {
			errorToDisplay = error;
		}

		this.showError( errorToDisplay );
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
			throw responseJson.data.error;
		}

		return responseJson.data.id;
	}
}

export default PaymentMethod;

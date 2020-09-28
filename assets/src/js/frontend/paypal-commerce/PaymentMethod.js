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
}

export default PaymentMethod;

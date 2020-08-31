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
		jQuery( document ).on( 'give_gateway_loaded', { self: this }, this.onGatewayLoadBoot );

		if ( DonationForm.isPayPalCommerceSelected( this.jQueryForm ) ) {
			this.renderPaymentMethodOption();
		}

		this.registerEvents();
	}

	/**
	 * Render paypal buttons when reload payment gateways.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} evt Event object.
	 * @param {*} response Form fields HTML for gateway.
	 * @param {string} formIdAttr Form Id attribute value.
	 */
	onGatewayLoadBoot( evt, response, formIdAttr ) {
		const self = evt.data.self;
		if ( formIdAttr === self.form.getAttribute( 'id' ) && DonationForm.isPayPalCommerceSelected( self.jQueryForm ) ) {
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
}

export default PaymentMethod;

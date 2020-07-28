/* globals jQuery, paypal, Give */
import DonationForm from './DonationForm';

/**
 * PayPal Smart Buttons.
 */
class SmartButtons {
	/**
	 * Constructor.
	 *
	 * @since 2.8.0
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
	 * @since 2.8.0
	 */
	boot() {
		jQuery( document ).on( 'give_gateway_loaded', { self: this }, this.onGatewayLoadBoot );

		if ( DonationForm.isPayPalCommerceSelected( this.jQueryForm ) ) {
			this.renderSmartButtons();
		}
	}

	/**
	 * Render paypal buttons when reload payment gateways.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} evt Event object.
	 * @param {*} response Form fields HTML for gateway.
	 * @param {string} formIdAttr Form Id attribute value.
	 */
	onGatewayLoadBoot( evt, response, formIdAttr ) {
		const self = evt.data.self;
		if ( formIdAttr === self.form.getAttribute( 'id' ) && DonationForm.isPayPalCommerceSelected( self.jQueryForm ) ) {
			self.renderSmartButtons();
		}
	}

	/**
	 * Render smart buttons.
	 *
	 * @since 2.8.0
	 */
	renderSmartButtons() {
		const onInitHandler = this.onInitHandler.bind( this );
		const onClickHandler = this.onClickHandler.bind( this );
		const createOrderHandler = this.createOrderHandler.bind( this );
		const onApproveHandler = this.onApproveHandler.bind( this );

		paypal.Buttons( {
			onInit: onInitHandler,
			onClick: onClickHandler,
			createOrder: createOrderHandler,
			onApprove: onApproveHandler,
		} ).render( this.form.querySelector( '#give-paypal-smart-buttons-wrap div' ) );
	}

	/**
	 * On init event handler for smart buttons.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 */
	onInitHandler( data, actions ) { // eslint-disable-line
		// Keeping this for future reference.
	}

	/**
	 * On click event handler for smart buttons.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return wther or not open PayPal checkout window.
	 */
	onClickHandler(data, actions) { // eslint-disable-line
		if ( ! Give.form.fn.isDonationFormHtml5Valid( this.form, true ) ) {
			return actions.reject();
		}

		return Give.form.fn.isDonorFilledValidData( this.form )
			.then( res => {
				if ( 'success' === res ) {
					return actions.resolve();
				}

				return actions.reject();
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
	createOrderHandler(data, actions) { // eslint-disable-line
		// eslint-disable-next-line
		return fetch(`${this.ajaxurl}?action=give_paypal_commerce_create_order`, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} ).then( function( res ) {
			return res.json();
		} ).then( function( res ) {
			return res.data.id;
		} );
	}

	/**
	 * On approve event handler for smart buttons.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return whether or not PayPal payment captured.
	 */
	onApproveHandler( data, actions ) {
		// eslint-disable-next-line
		return fetch(`${this.ajaxurl}?action=give_paypal_commerce_approve_order&order=` + data.orderID, {
			method: 'post',
		} ).then( function( res ) {
			return res.json();
		} ).then( function( res ) {
			// Three cases to handle:
			//   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
			//   (2) Other non-recoverable errors -> Show a failure message
			//   (3) Successful transaction -> Show a success / thank you message

			// Your server defines the structure of 'orderData', which may differ
			const errorDetail = Array.isArray( res.data.order.details ) && res.data.order.details[ 0 ];
			const orderData = res.data.order;

			if ( errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED' ) {
				// Recoverable state, see: "Handle Funding Failures"
				// https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
				return actions.restart();
			}

			if ( errorDetail ) {
				let msg = 'Sorry, your transaction could not be processed.';
				if ( errorDetail.description ) {
					msg += '\n\n' + errorDetail.description;
				}
				if ( orderData.debug_id ) {
					msg += ' (' + orderData.debug_id + ')';
				}
				// Show a failure message
				return alert(msg); // eslint-disable-line
			}

			DonationForm.attachOrderIdToForm( this.form, orderData.id )
				.then( () => {
					this.form.submit();
				} );
		} );
	}
}

export default SmartButtons;

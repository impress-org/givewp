/* globals paypal, Give, FormData, givePayPalCommerce */
import DonationForm from './DonationForm';
import PaymentMethod from './PaymentMethod';
import CustomCardFields from './CustomCardFields';
import AdvancedCardFields from './AdvancedCardFields';

/**
 * PayPal Smart Buttons.
 */
class SmartButtons extends PaymentMethod {
	constructor( form ) {
		super( form );

		this.ccFieldsContainer = this.form.querySelector( '[id^="give_cc_fields-"]' );
	}
	/**
	 * Get smart button container.
	 *
	 * @since 2.9.0
	 *
	 * @return {object} Smart button container selector.
	 */
	getButtonContainer() {
		const smartButtonWrap = document.createElement( 'div' );
		this.ccFieldsContainer = this.form.querySelector( '[id^="give_cc_fields-"]' ); // Refresh cc field container selector.

		smartButtonWrap.setAttribute( 'id', '#give-paypal-commerce-smart-buttons-wrap' );

		return this.ccFieldsContainer.insertBefore( smartButtonWrap, this.ccFieldsContainer.querySelector( '[id^=give-card-number-wrap-]' ) );
	}

	/**
	 * Render smart buttons.
	 *
	 * @since 2.9.0
	 */
	renderPaymentMethodOption() {
		this.smartButtonContainer = this.getButtonContainer();

		if ( ! this.smartButtonContainer ) {
			return;
		}

		const onInitHandler = this.onInitHandler.bind( this );
		const onClickHandler = this.onClickHandler.bind( this );
		const createOrderHandler = this.createOrderHandler.bind( this );
		const onApproveHandler = this.onApproveHandler.bind( this );

		paypal.Buttons( {
			onInit: onInitHandler,
			onClick: onClickHandler,
			createOrder: createOrderHandler,
			onApprove: onApproveHandler,
			onError: function( error ) {
				// eslint-disable-next-line no-console
				console.log( 'caught error', error );
			},
			style: {
				layout: 'vertical',
				size: 'responsive',
				shape: 'rect',
				label: 'paypal',
				color: 'gold',
				tagline: false,
			},
		} ).render( this.smartButtonContainer );

		DonationForm.toggleDonateNowButton( this.form );
	}

	/**
	 * On init event handler for smart buttons.
	 *
	 * @since 2.9.0
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
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return whether or not open PayPal checkout window.
	 */
	async onClickHandler(data, actions) { // eslint-disable-line
		const formData = new FormData( this.form );

		if ( AdvancedCardFields.canShow() ) {
			formData.delete( 'card_name' );
			formData.delete( 'card_cvc' );
			formData.delete( 'card_number' );
			formData.delete( 'card_expiry' );
		}

		Give.form.fn.removeErrors( this.jQueryForm );
		const result = await Give.form.fn.isDonorFilledValidData( this.form, formData );

		if ( 'success' === result ) {
			if ( DonationForm.isRecurringDonation( this.form ) ) {
				this.submitDonationForm();

				return actions.reject();
			}

			return actions.resolve();
		}

		Give.form.fn.addErrors( this.jQueryForm, result );
		return actions.reject();
	}

	/**
	 * On approve event handler for smart buttons.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {*} Return whether or not PayPal payment captured.
	 */
	async onApproveHandler( data, actions ) {
		Give.form.fn.showProcessingState();
		Give.form.fn.disable( this.jQueryForm, true );
		Give.form.fn.removeErrors( this.jQueryForm );

		// eslint-disable-next-line
		const response = await fetch( `${ this.ajaxurl }?action=give_paypal_commerce_approve_order&order=` + data.orderID, {
			method: 'post',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} );
		const responseJson = await response.json();

		// Three cases to handle:
		//   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
		//   (2) Other non-recoverable errors -> Show a failure message
		//   (3) Successful transaction -> Show a success / thank you message

		let errorDetail = {};
		if ( ! responseJson.success ) {
			Give.form.fn.disable( this.jQueryForm, false );
			Give.form.fn.hideProcessingState();

			if ( null === responseJson.data.error ) {
				DonationForm.addErrors( this.jQueryForm, Give.form.fn.getErrorHTML( [ { message: givePayPalCommerce.defaultDonationCreationError } ] ) );
				return;
			}

			errorDetail = responseJson.data.error.details[ 0 ];
			if ( errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED' ) {
				Give.form.fn.hideProcessingState();
				Give.form.fn.disable( this.jQueryForm, false );

				// Recoverable state, see: "Handle Funding Failures"
				// https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
				return actions.restart();
			}

			DonationForm.addErrors( this.jQueryForm, Give.form.fn.getErrorHTML( [ { message: errorDetail.description } ] ) );
			return;
		}

		const orderData = responseJson.data.order;
		await DonationForm.attachOrderIdToForm( this.form, orderData.id );

		this.submitDonationForm();
	}

	/**
	 * Submit donation form.
	 *
	 * @since 2.9.0
	 */
	submitDonationForm() {
		// Do not submit  empty or filled Name credit card field with form.
		// If we do that we will get `empty_card_name` error or other.
		// We are removing this field before form submission because this donation processed with smart button.
		this.jQueryForm.off( 'submit' );
		this.removeCreditCardFields();
		this.form.submit();
	}

	/**
	 * Remove Card fields.
	 *
	 * @since 2.9.0
	 */
	removeCreditCardFields() {
		// Remove custom card fields.
		if ( AdvancedCardFields.canShow() ) {
			this.jQueryForm.find( 'input[name="card_name"]' ).parent().remove();
			this.ccFieldsContainer.querySelector( '.separator-with-text' ).remove(); // Remove separator.

			const $customCardFields = new CustomCardFields( this.form );

			for ( const key in $customCardFields.cardFields ) {
				$customCardFields.cardFields[ key ].el.parentElement.remove();
			}
		}
	}
}

export default SmartButtons;

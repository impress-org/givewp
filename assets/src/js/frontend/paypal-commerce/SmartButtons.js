/* globals paypal, Give, FormData, givePayPalCommerce */
import DonationForm from './DonationForm';
import PaymentMethod from './PaymentMethod';
import CustomCardFields from './CustomCardFields';
import AdvancedCardFields from './AdvancedCardFields';

/**
 * PayPal Smart Buttons.
 */
class SmartButtons extends PaymentMethod {
	/**
	 * Setup properties.
	 *
	 * @since 2.9.0
	 */
	setupProperties() {
		this.ccFieldsContainer = this.form.querySelector( '[id^="give_cc_fields-"]' );
		this.recurringChoiceHiddenField = this.form.querySelector( 'input[name="_give_is_donation_recurring"]' );
		this.smartButton = null;
	}

    /**
     * Check if smart buttons can be shown.
     * @unreleased
     * @return {boolean}
     */
    static canShow() {
        return ['smart-buttons', 'auto'].includes(givePayPalCommerce.paymentFieldType);
    }

	/**
	 * Get smart button container.
	 *
	 * @since 2.9.0
	 *
	 * @return {object} Smart button container selector.
	 */
	getButtonContainer() {
		this.ccFieldsContainer = this.form.querySelector( '[id^="give_cc_fields-"]' ); // Refresh cc field container selector.
		const oldSmartButtonWrap = this.ccFieldsContainer.querySelector( '#give-paypal-commerce-smart-buttons-wrap' );

		if ( oldSmartButtonWrap ) {
			return oldSmartButtonWrap;
		}

		const smartButtonWrap = document.createElement( 'div' );
		const separator = this.ccFieldsContainer.querySelector( '.separator-with-text' );
		smartButtonWrap.setAttribute( 'id', 'give-paypal-commerce-smart-buttons-wrap' );
		const cardNumberWarp = this.ccFieldsContainer.querySelector( '[id^=give-card-number-wrap-]' );

		return this.ccFieldsContainer.insertBefore( smartButtonWrap, separator ? separator : cardNumberWarp );
	}

	/**
	 * Render smart buttons.
	 *
	 * @since 2.9.0
	 * @return {object} Return Promise
	 */
	renderPaymentMethodOption() {
		this.smartButtonContainer = this.getButtonContainer();

		if ( this.smartButton ) {
			this.smartButton.close();
		}

		const options = {
			onInit: this.onInitHandler.bind( this ),
			onClick: this.onClickHandler.bind( this ),
			createOrder: this.createOrderHandler.bind( this ),
			onApprove: this.orderApproveHandler.bind( this ),
			style: {
				layout: 'vertical',
				size: 'responsive',
				shape: 'rect',
				label: 'paypal',
				color: 'gold',
				tagline: false,
			},
			onError: ( error ) =>{
				this.displayErrorMessage( error );
			},
		};

		if ( DonationForm.isRecurringDonation( this.form ) ) {
			options.createSubscription = this.creatSubscriptionHandler.bind( this );
			options.onApprove = this.subscriptionApproveHandler.bind( this );

			delete options.createOrder;
		}

		DonationForm.toggleDonateNowButton( this.form );

		this.smartButton = paypal.Buttons( options );

		return this.smartButton.render( this.smartButtonContainer );
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
			return actions.resolve();
		}

		this.showError( result );
		return actions.reject();
	}

	/**
	 * Create subscription event handler for smart buttons.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {Promise<unknown>} Return PayPal order id.
	 */
	async creatSubscriptionHandler( data, actions ) {
		Give.form.fn.removeErrors( this.jQueryForm );

		// eslint-disable-next-line
		const response = await fetch( `${ this.ajaxurl }?action=give_paypal_commerce_create_plan_id`, {
			method: 'POST',
			body: DonationForm.getFormDataWithoutGiveActionField( this.form ),
		} );

		const responseJson = await response.json();

		if ( ! responseJson.success ) {
			throw responseJson.data.error;
		}

		return actions.subscription.create( { plan_id: responseJson.data.id } );
	}

	/**
	 * Subscription approve event handler for smart buttons.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {*} Return whether or not PayPal payment captured.
	 */
	async subscriptionApproveHandler( data, actions ) { // eslint-disable-line
		Give.form.fn.showProcessingState( window.givePayPalCommerce.textForOverlayScreen );
		await DonationForm.addFieldToForm( this.form, data.subscriptionID, 'payPalSubscriptionId' );

		this.submitDonationForm();
	}

	/**
	 * Order approve event handler for smart buttons.
	 *
	 * @since 2.9.0
	 *
	 * @param {object} data PayPal button data.
	 * @param {object} actions PayPal button actions.
	 *
	 * @return {*} Return whether or not PayPal payment captured.
	 */
	async orderApproveHandler( data, actions ) {
		Give.form.fn.showProcessingState( window.givePayPalCommerce.textForOverlayScreen );
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

			this.displayErrorMessage( responseJson.data.error, true );

			errorDetail = responseJson.data.error.details[ 0 ];
			if ( errorDetail && errorDetail.issue === 'INSTRUMENT_DECLINED' ) {
				// Recoverable state, see: "Handle Funding Failures"
				// https://developer.paypal.com/docs/checkout/integration-features/funding-failure/
				return actions.restart();
			}

			return;
		}

		const orderData = responseJson.data.order;
		await DonationForm.addFieldToForm( this.form, orderData.id, 'payPalOrderId' );

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

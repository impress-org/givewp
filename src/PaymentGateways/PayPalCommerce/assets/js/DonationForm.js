/* globals Give, Promise  */
class DonationForm {
	/**
	 * Get form Data.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} $form Form selector.
	 *
	 * @return {FormData} FormData object of form entries.
	 */
	static getFormDataWithoutGiveActionField( $form ) {
		const formData = new FormData( $form ); // eslint-disable-line

		formData.delete( 'give_action' );

		return formData;
	}

	/**
	 * Add PayPal order id as hidden type input field to donation form.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} $form Form selector.
	 * @param {string} orderId PayPal order id.
	 *
	 * @return {Promise} Promise of appending hidden input field to donation form.
	 */
	static attachOrderIdToForm( $form, orderId ) {
		const input = document.createElement( 'input' );

		input.type = 'hidden';
		input.name = 'payPalOrderId';
		input.value = orderId;

		return new Promise( ( resolve, reject ) => { // eslint-disable-line
			resolve( $form.appendChild( input ) );
		} );
	}

	/**
	 * Check if donor selected PayPal Commerce payment gateway or not.
	 *
	 * @since 2.8.0
	 *
	 * @param {object} $form Form object.
	 *
	 * @return {boolean} Return whether or not donor selected PayPal Commerce payment gateway.
	 */
	static isPayPalCommerceSelected( $form ) {
		return 'paypal-commerce' === Give.form.fn.getGateway( $form );
	}
}

export default DonationForm;

class GiveWpPayPal {
	/**
	 * Get form Data.
	 *
	 * @since 2.8.0
	 *
	 * @param $form
	 * @return {FormData}
	 */
	static getFormDataFormHttpRequest( $form ) {
		const formData = new FormData( $form );

		formData.delete( 'give_action' );

		return formData;
	}

	/**
	 * Add PayPal order id as hidden type input field to donation form.
	 *
	 * @since 2.8.0
	 *
	 * @param $form
	 * @param orderId
	 * @return {*}
	 */
	static attachOrderIdToForm( $form, orderId ) {
		const input = document.createElement( 'input' );

		input.type = 'hidden';
		input.name = 'payPalOrderId';
		input.value = orderId;

		return new Promise( ( resolve, reject ) => {
			resolve( $form.appendChild( input ) );
		} );
	}
}

/* globals paypal, Give */
document.addEventListener( 'DOMContentLoaded', () => {
	const computedStyle = window.getComputedStyle( document.querySelector( '#give-card-name-wrap input[name="card_name"]' ), null ),
		  inputStyle = {
			  'font-size': computedStyle.getPropertyValue( 'font-size' ),
			  'font-family': computedStyle.getPropertyValue( 'font-family' ),
			  color: computedStyle.getPropertyValue( 'color' ),
		  },
		  $form = document.querySelector( '#give-form-510-1' ),
		  formData = new FormData( $form );

	// Check if card fields are eligible to render for the buyer's country. The card fields are not eligible in all countries where buyers are located.
	if ( paypal.HostedFields.isEligible() === true ) {
		paypal.HostedFields.render( {
			createOrder: function( data, actions ) {
				return fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_create_order`, {
					method: 'POST',
					body: formData,
				} ).then( function( res ) {
					return res.json();
				} ).then( function( res ) {
					return res.data.id;
				} );
			},
			styles: {
				input: inputStyle,
			},
			fields: {
				number: {
					selector: '#give-card-number-field-510-1',
					placeholder: 'Card Number',
				},
				cvv: {
					selector: '#give-card-cvc-field-510-1',
					placeholder: 'CVV',
				},
				expirationDate: {
					selector: '#give-card-expiration-field-510-1',
					placeholder: 'MM/YY',
				},
			},
		} ).then( hostedFields => {
			jQuery( $form ).on( 'submit', event => {
				event.preventDefault();
				hostedFields.submit().then( payload => {
					console.log( payload );

					return fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_approve_order&order=` + payload.orderId, {
						method: 'POST',
					} ).then( function( res ) {
						return res.json();
					} ).then( res => {
						if ( true !== res.success ) {
							alert( 'Something went wrong' );
						}
					} );
				} );

				return false;
			} );
		} );
	}

	paypal.Buttons( {
		// Call your server to set up the transaction
		createOrder: function( data, actions ) {
			return fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_create_order`, {
				method: 'POST',
				body: GiveWpPayPal.getFormDataFormHttpRequest( $form ),
			} ).then( function( res ) {
				return res.json();
			} ).then( function( res ) {
				return res.data.id;
			} );
		},

		// Call your server to finalize the transaction
		onApprove: function( data, actions ) {
			return fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_approve_order&order=` + data.orderID, {
				method: 'post',
			} ).then( function( res ) {
				return res.json();
			} ).then( function( res ) {
				// Three cases to handle:
				//   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
				//   (2) Other non-recoverable errors -> Show a failure message
				//   (3) Successful transaction -> Show a success / thank you message

				// Your server defines the structure of 'orderData', which may differ
				const errorDetail = Array.isArray( res.data.order.details ) && res.data.order.details[ 0 ],
					orderData = res.data.order;

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
					return alert( msg );
				}

				GiveWpPayPal.attachOrderIdToForm( $form, orderData.id )
					.then( () => {
						$form.submit();
					} );
			} );
		},
	} ).render( '#give-paypal-smart-buttons-field-510-1' );
} );

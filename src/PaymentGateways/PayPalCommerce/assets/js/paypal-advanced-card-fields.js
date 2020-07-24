class GiveWpPayPal{
	static getFormData( $formId ) {
		return new FormData( $formId );
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
				body: GiveWpPayPal.getFormData( $form ),
			} ).then( function( res ) {
				return res.json();
			} ).then( function( res ) {
				return res.data.id;
			} );
		},

		// Call your server to finalize the transaction
		onApprove: function( data, actions ) {
			console.log( data, actions );

			return fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_approve_order&order=` + data.orderID, {
				method: 'post',
			} ).then( function( res ) {
				return res.json();
			} ).then( function( orderData ) {
				// Three cases to handle:
				//   (1) Recoverable INSTRUMENT_DECLINED -> call actions.restart()
				//   (2) Other non-recoverable errors -> Show a failure message
				//   (3) Successful transaction -> Show a success / thank you message

				// Your server defines the structure of 'orderData', which may differ
				const errorDetail = Array.isArray( orderData.details ) && orderData.details[ 0 ];

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

				// Show a success message to the buyer  orderData.payer.name.given_name
				alert( 'Transaction completed' + orderData.payer.name.given_name );
			} );
		},
	} ).render( '#give-paypal-smart-buttons-field-510-1' );
} );

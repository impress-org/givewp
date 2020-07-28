
/* globals paypal, Give */
document.addEventListener( 'DOMContentLoaded', () => {
	// Run script only if form exits on page.
	// if ( ! Give.form.fn.isFormExist() ) {
	// 	return;
	// }
	//
	// const computedStyle = window.getComputedStyle( document.querySelector( '#give-card-name-wrap input[name="card_name"]' ), null ),
	// 	inputStyle = {
	// 		'font-size': computedStyle.getPropertyValue( 'font-size' ),
	// 		'font-family': computedStyle.getPropertyValue( 'font-family' ),
	// 		color: computedStyle.getPropertyValue( 'color' ),
	// 	},
	// 	$form = document.querySelector( '#give-form-510-1' ),
	// 	$jForm = jQuery( $form );

	// // On form submit prevent submission for PayPal commerce.
	// $jForm.on( 'submit', function( e ) {
	// 	if ( ! GiveWpPayPal.isPayPalCommerceSelected( $jForm ) || ! GiveWpPayPal.isDonationFormHtml5Valid( $form ) ) {
	// 		return true;
	// 	}
	//
	// 	e.preventDefault();
	//
	// 	return false;
	// } );

	// Check if card fields are eligible to render for the buyer's country. The card fields are not eligible in all countries where buyers are located.
	// if ( paypal.HostedFields.isEligible() === true ) {
	// 	paypal.HostedFields.render( {
	// 		createOrder: function( data, actions ) { // eslint-disable-line
	// 			// eslint-disable-next-line
	// 			return fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_create_order`, {
	// 				method: 'POST',
	// 				body: GiveWpPayPal.getFormDataFormHttpRequest( $form ),
	// 			} ).then( function( res ) {
	// 				return res.json();
	// 			} ).then( function( res ) {
	// 				return res.data.id;
	// 			} );
	// 		},
	// 		styles: {
	// 			input: inputStyle,
	// 		},
	// 		fields: {
	// 			number: {
	// 				selector: '#give-card-number-field-510-1',
	// 				placeholder: 'Card Number',
	// 			},
	// 			cvv: {
	// 				selector: '#give-card-cvc-field-510-1',
	// 				placeholder: 'CVV',
	// 			},
	// 			expirationDate: {
	// 				selector: '#give-card-expiration-field-510-1',
	// 				placeholder: 'MM/YY',
	// 			},
	// 		},
	// 	} ).then( hostedFields => {
	// 		jQuery( $form ).on( 'submit', event => {
	// 			event.preventDefault();
	// 			hostedFields.submit().then( payload => {
	// 				// eslint-disable-next-line
	// 				return fetch( `${ Give.fn.getGlobalVar( 'ajaxurl' ) }?action=give_paypal_commerce_approve_order&order=` + payload.orderId, {
	// 					method: 'POST',
	// 				} ).then( function( res ) {
	// 					return res.json();
	// 				} ).then( res => {
	// 					if ( true !== res.success ) {
	// 						alert( 'Something went wrong' ); // eslint-disable-line
	// 					}
	// 				} );
	// 			} );
	//
	// 			return false;
	// 		} );
	// 	} );
	// }
} );

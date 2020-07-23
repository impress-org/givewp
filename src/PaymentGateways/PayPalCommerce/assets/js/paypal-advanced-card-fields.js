/* globals paypal, Give */
document.addEventListener( 'DOMContentLoaded', () => {
	// Check if card fields are eligible to render for the buyer's country. The card fields are not eligible in all countries where buyers are located.
	if ( paypal.HostedFields.isEligible() === true ) {
		const computedStyle = window.getComputedStyle( document.querySelector( '#give-card-name-wrap input[name="card_name"]' ), null ),
			inputStyle = {
				'font-size': computedStyle.getPropertyValue( 'font-size' ),
				'font-family': computedStyle.getPropertyValue( 'font-family' ),
				color: computedStyle.getPropertyValue( 'color' ),
			},
			$form = document.querySelector( '#give-form-510-1' ),
			formData = new FormData( $form );

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

	paypal.Buttons().render( '#give-paypal-smart-buttons-field-510-1' );
} );

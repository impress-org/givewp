/* globals paypal */
document.addEventListener( 'DOMContentLoaded', () => {
	// Check if card fields are eligible to render for the buyer's country. The card fields are not eligible in all countries where buyers are located.
	if ( paypal.HostedFields.isEligible() === true ) {
		paypal.HostedFields.render( {
			createOrder: function( data, actions ) {
				return fetch( '/my-server/create-order', {
					method: 'POST',
				} ).then( function( res ) {
					return res.json();
				} ).then( function( data ) {
					return data.id;
				} );
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
			document.querySelector( '#give-form-510-1' ).addEventListener( 'submit', event => {
				event.preventDefault();
				hostedFields.submit().then( payload => {
					return fetch( '/my-server/handle-approve/' + payload.orderId, {
						method: 'POST',
					} ).then( response => {
						if ( ! response.ok ) {
							alert( 'Something went wrong' );
						}
					} );
				} );
			} );
		} );
	}
} );

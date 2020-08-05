/**
 * Accessible Block Links
 *
 * Problem: Hyperlink a component while maintaining screen-reader accessibility and the ability to select text.
 * Solution: Use progressive enhancement to conditionally trigger the target anchor element.
 *
 * @link https://css-tricks.com/block-links-the-search-for-a-perfect-solution/
 */

Array.from( document.querySelectorAll( '.setup-item' ) ).forEach( ( setupItem ) => {
	const actionAnchor = setupItem.querySelector( '.js-action-link' );

	if ( actionAnchor ) {
		actionAnchor.addEventListener( 'click', ( e ) => e.stopPropagation() );
		setupItem.style.cursor = 'pointer';
		setupItem.addEventListener( 'click', ( event ) => { // eslint-disable-line no-unused-vars
			if ( ! window.getSelection().toString() ) {
				actionAnchor.click();
			}
		} );
	}
} );

document.getElementById( 'stripeWebhooksCopyHandler' ).addEventListener( 'click', function() {
	const webhooksURL = document.getElementById( 'stripeWebhooksCopy' );
	webhooksURL.disabled = false; // Copying requires the input to not be disabled.
	webhooksURL.select();
	document.execCommand( 'copy' );
	webhooksURL.disabled = true;

	const icon = document.getElementById( 'stripeWebhooksCopyIcon' );
	icon.classList.remove( 'fa-clipboard' );
	icon.classList.add( 'fa-clipboard-check' );
	setTimeout( function() {
		icon.classList.remove( 'fa-clipboard-check' );
		icon.classList.add( 'fa-clipboard' );
	}, 3000 );
} );

function pollStripeWebhookRecieved() {
	const endpoint = wpApiSettings.root + 'give-api/v2/onboarding/stripe-webhook-recieved';
	jQuery.get( endpoint, function( data ) {
		if ( undefined === typeof data.webhookRecieved || ! data.webhookRecieved ) {
			setTimeout( pollStripeWebhookRecieved, 5000 );
		} else {
			document.getElementById( 'stripeWebhooksConfigureButton' ).classList.add( 'hidden' );
			document.getElementById( 'stripeWebhooksConfigureConfirmed' ).classList.remove( 'hidden' );
		}
	} );
}
pollStripeWebhookRecieved();


/* globals jQuery */
import DonationForm from './DonationForm';
import SmartButtons from './paypal-smart-buttons';

document.addEventListener( 'DOMContentLoaded', () => {
	const $formWraps = document.querySelectorAll( '.give-form-wrap' );

	$formWraps.forEach( $formWrap => {
		const $form = $formWrap.querySelector( '.give-form' );
		const smartButtons = new SmartButtons( $form );
		smartButtons.boot();
	} );

	// On form submit prevent submission for PayPal commerce.
	// Form submission will be take care internally by smart buttons or advanced card fields.
	jQuery( 'form.give-form' ).on( 'submit', e => {
		if ( ! DonationForm.isPayPalCommerceSelected( jQuery( this ) ) || ! DonationForm.isDonationFormHtml5Valid( jQuery( this ).get( 0 ) ) ) {
			return true;
		}

		e.preventDefault();

		return false;
	} );
} );

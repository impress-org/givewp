import SmartButtons from './paypal-smart-buttons';

document.addEventListener( 'DOMContentLoaded', () => {
	const $formWraps = document.querySelectorAll( '.give-form-wrap' );

	$formWraps.forEach( $formWrap => {
		const $form = $formWrap.querySelector( '.give-form' );
		const smartButtons = new SmartButtons( $form );
		smartButtons.boot();
	} );
} );

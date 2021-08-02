/**
 * Disconnect stripe account
 *
 * This will be used to disconnect any Stripe account from the list
 *
 * @unreleased
 */
const { __, sprintf } = wp.i18n;

window.addEventListener( 'DOMContentLoaded', function() {
	const disconnectBtns = Array.from( document.querySelectorAll( '.give-stripe-disconnect-account-btn' ) );

	if ( ! disconnectBtns.length ) {
		return
	}

	disconnectBtns.forEach( ( button ) => {
		button.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			const button = e.target;
			const parentElement = e.target.parentElement.parentElement.parentElement.parentElement;
			const isGlobalDefaultAccount = parentElement.classList.contains('give-global-default-account');
			let modalMessage = __( 'Are you sure you want to disconnect this Stripe account?', 'give' );

			if( isGlobalDefaultAccount ) {
				modalMessage = sprintf(
					__( 'This Stripe account is selected as Global Default account, so you can disconnect this account.', 'give' ),
				)
			}

			new Give.modal.GiveConfirmModal( {
				type: 'alert',
				classes: {
					modalWrapper: 'give-modal--warning',
				},
				modalContent: {
					title: __( 'Disconnect Stripe Account', 'give' ),
					desc: modalMessage,
				},
				callbacks: {
					open: () => {
						if( isGlobalDefaultAccount ) {
							jQuery.magnificPopup.instance.content[0].querySelector('.give-modal__controls .give-popup-confirm-button').disabled = true;
						}
					}
				},
				successConfirm: () => {
					fetch( button.getAttribute('href') )
						.then( response => response.json() )
						.then( response => {
							if( response.success ) {
								window.location.reload()
							}
						} );
				},
			} ).render();
		} );
	} );
});

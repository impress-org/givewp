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
			let modalTitle = __( 'Disconnect Stripe Account', 'give' );
			let modalMessage = __( 'Are you sure you want to disconnect this Stripe account?', 'give' );

			if( isGlobalDefaultAccount ) {
				modalTitle = __( 'Cannot Disconnect Global Account', 'give' );
				modalMessage = sprintf(
					__( 'This Stripe account is set as the Global Default account that other donation forms may be using. To disconnect this account please go to the Stripe settings screen.', 'give' ),
				)
			}

			new Give.modal.GiveConfirmModal( {
				type: 'alert',
				classes: {
					modalWrapper: `give-modal--${ isGlobalDefaultAccount ? 'error' : 'warning' }`,
				},
				modalContent: {
					title: modalTitle,
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

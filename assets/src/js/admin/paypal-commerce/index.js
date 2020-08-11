window.addEventListener( 'DOMContentLoaded', function() {
	const donationStatus = document.getElementById( 'give-payment-status' );

	if ( donationStatus ) {
		donationStatus.addEventListener( 'change', ( event ) => {
			const paypalDonationsCheckbox = document.getElementById( 'give-paypal-donations-opt-refund' );

			if ( null === paypalDonationsCheckbox ) {
				return;
			}

			paypalDonationsCheckbox.checked = false;

			// If donation status is complete, then show refund checkbox
			if ( 'refunded' === event.target.value ) {
				document.getElementById( 'give-paypal-donations-opt-refund-wrap' ).style.display = 'block';
			} else {
				document.getElementById( 'give-paypal-donations-opt-refund-wrap' ).style.display = 'none';
			}
		} );
	}
} );

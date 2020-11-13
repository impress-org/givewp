jQuery( document ).ready( function() {
	if ( typeof URLSearchParams !== 'undefined' && window.history ) {
		const currentParams = new URLSearchParams( window.location.search );

		if ( ! ( currentParams.has( 'give-run-migration' ) || currentParams.has( 'give-clear-update' ) ) ) {
			return;
		}

		currentParams.delete( 'give-run-migration' );
		currentParams.delete( 'give-clear-update' );

		const newUrl = `${ window.location.origin }${ window.location.pathname }?${ currentParams }`;

		window.history.replaceState( {}, '', newUrl );
	}
} );


const observers = [];

function subscribe( action, callback ) {
	observers.push( {
		action,
		callback,
	} );
}

export function observeAction( action ) {
	observers.filter( ( observer ) => {
		return observer.action === action.type;
	} ).map( ( observer ) => {
		observer.callback( action );
	} );
}

/**
 * The Preview step creates horizontal movement due to
 * the change in form height. In order to normalize
 * visual movement, set the overflow-y to scroll.
 *
 * Starting with the first step, after the introduction, set the overflow to scroll.
 */
subscribe( 'GO_TO_STEP', ( action ) => {
	if ( action.payload.step === 1 ) {
		document.body.style[ 'overflow-y' ] = 'scroll';
	}
} );

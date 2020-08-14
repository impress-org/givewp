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
 * Example
 */
subscribe( 'GO_TO_STEP', ( action ) => {
	console.log( action.payload.step );
} );

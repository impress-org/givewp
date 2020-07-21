// Import utilities
import { getWindowData } from '../../utils';

export const reducer = ( state, action ) => {
	switch ( action.type ) {
		case 'GO_TO_STEP':
			if ( action.payload.step <= state.lastStep ) {
				return {
					...state,
					currentStep: action.payload.step,
				};
			}
			window.location.href = getWindowData( 'setupUrl' );
			break;
		default:
			return state;
	}
};

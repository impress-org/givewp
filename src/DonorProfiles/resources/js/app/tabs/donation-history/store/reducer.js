import { initialState } from './initialState';

export const reducer = ( state = initialState, action ) => {
	switch ( action.type ) {
		case 'SET_DONATIONS':
			return {
				...state,
				donations: action.payload.donations,
			};
		case 'SET_QUERYING':
			return {
				...state,
				querying: action.payload.querying,
			};
		default:
			return state;
	}
};

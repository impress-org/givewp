import { initialState } from './initialState';

export const reducer = ( state = initialState, action ) => {
	switch ( action.type ) {
		case 'SET_SUBSCRIPTIONS':
			return {
				...state,
				subscriptions: action.payload.subscriptions,
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

import { initialState } from './initialState';

export const reducer = ( state = initialState, action ) => {
	switch ( action.type ) {
		case 'SET_ANNUAL_RECEIPTS':
			return {
				...state,
				annualReceipts: action.payload.annualReceipts,
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

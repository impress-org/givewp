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
		case 'SET_ERROR':
			return {
				...state,
				error: action.payload.error,
			};
		case 'SET_COUNT':
			return {
				...state,
				count: action.payload.count,
			};
		case 'SET_AVERAGE':
			return {
				...state,
				average: action.payload.average,
			};
		case 'SET_REVENUE':
			return {
				...state,
				revenue: action.payload.revenue,
			};
		case 'SET_CURRENCY':
			return {
				...state,
				currency: action.payload.currency,
			};
		default:
			return state;
	}
};

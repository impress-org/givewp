import { initialState } from './initialState';

export const reducer = ( state = initialState, action ) => {
	switch ( action.type ) {
		case 'SET_ACTIVE_TAB':
			return {
				...state,
				activeTab: action.payload.tab,
			};
		case 'ADD_TAB':
			const registeredTabs = Object.assign( {}, state.tabs );
			registeredTabs[ action.payload.tab.slug ] = action.payload.tab;

			return {
				...state,
				tabs: registeredTabs,
			};
		case 'SET_PROFILE':
			return {
				...state,
				profile: action.payload.profile,
			};
		case 'SET_APPLICATION_ERROR':
			return {
				...state,
				applicationError: action.payload.error,
			};
		case 'SET_STATES':
			return {
				...state,
				states: action.payload.states,
			};
		case 'SET_FETCHING_STATES':
			return {
				...state,
				fetchingStates: action.payload.fetchingStates,
			};
		default:
			return state;
	}
};

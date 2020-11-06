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
		default:
			return state;
	}
};

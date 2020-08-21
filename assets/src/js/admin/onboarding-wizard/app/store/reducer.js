import {
	saveSettingWithOnboardingAPI,
	fetchStatesListWithOnboardingAPI,
} from '../../utils';

import { observeAction } from './observers';

export const reducer = ( state, action ) => {
	observeAction( action );

	switch ( action.type ) {
		case 'GO_TO_STEP':
			return {
				...state,
				currentStep: action.payload.step,
			};
		case 'SET_USER_TYPE':
			saveSettingWithOnboardingAPI( 'user_type', action.payload.type );
			return {
				...state,
				configuration: { ...state.configuration,
					userType: action.payload.type,
				},
			};
		case 'SET_CAUSE_TYPE':
			saveSettingWithOnboardingAPI( 'cause_type', action.payload.type );
			return {
				...state,
				configuration: { ...state.configuration,
					causeType: action.payload.type,
				},
			};
		case 'SET_COUNTRY':
			saveSettingWithOnboardingAPI( 'base_country', action.payload.country );
			return {
				...state,
				configuration: { ...state.configuration,
					country: action.payload.country,
				},
			};
		case 'FETCH_STATE_LIST':
			fetchStatesListWithOnboardingAPI( action.payload.country, action.payload.dispatch );
			return {
				...state,
			};
		case 'SET_STATE_LIST':
			return {
				...state,
				statesList: action.payload.stateList,
			};
		case 'SET_FETCHING_STATES_LIST':
			return {
				...state,
				fetchingStatesList: action.payload.status,
			};
		case 'SET_STATE':
			saveSettingWithOnboardingAPI( 'base_state', action.payload.state );
			return {
				...state,
				configuration: { ...state.configuration,
					state: action.payload.state,
				},
			};
		case 'SET_CURRENCY':
			saveSettingWithOnboardingAPI( 'currency', action.payload.currency );
			return {
				...state,
				configuration: { ...state.configuration,
					currency: action.payload.currency,
				},
			};
		case 'SET_ADDONS':
			saveSettingWithOnboardingAPI( 'addons', action.payload.addons );
			return {
				...state,
				configuration: { ...state.configuration,
					addons: action.payload.addons,
				},
			};
		case 'SET_FEATURES':
			saveSettingWithOnboardingAPI( 'features', action.payload.features );
			return {
				...state,
				configuration: { ...state.configuration,
					features: action.payload.features,
				},
			};
		default:
			return state;
	}
};

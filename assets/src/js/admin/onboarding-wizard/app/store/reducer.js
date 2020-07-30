import { saveSettingWithOnboardingAPI, getStatesListWithOnboardingAPI, getCurrencyWithOnboardingAPI } from '../../utils';

export const reducer = ( state, action ) => {
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
			saveSettingWithOnboardingAPI( 'country', action.payload.country );
			const newStatesList = getStatesListWithOnboardingAPI( action.payload.country );
			const newCurrency = getCurrencyWithOnboardingAPI( action.payload.country );
			return {
				...state,
				statesList: newStatesList,
				configuration: { ...state.configuration,
					country: action.payload.country,
					currency: newCurrency,
				},
			};
		case 'SET_STATE':
			saveSettingWithOnboardingAPI( 'state_province', action.payload.state );
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
		case 'SET_FUNDRAISING_NEEDS':
			saveSettingWithOnboardingAPI( 'fundraising_needs', action.payload.fundraisingNeeds );
			return {
				...state,
				configuration: { ...state.configuration,
					fundraisingNeeds: action.payload.needs,
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

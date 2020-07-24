import { saveSettingWithOnboardingAPI, getStatesListWithOnboardingAPI, getCurrencyWithOnboardingAPI } from '../../utils';

export const reducer = async( state, action ) => {
	switch ( action.type ) {
		case 'GO_TO_STEP':
			return {
				...state,
				currentStep: action.payload.step,
			};
		case 'SET_USER_TYPE':
			await saveSettingWithOnboardingAPI( 'user_type', action.payload.type );
			return {
				...state,
				configuration: { ...state.configuration,
					userType: action.payload.type,
				},
			};
		case 'SET_CAUSE_TYPE':
			await saveSettingWithOnboardingAPI( 'cause_type', action.payload.type );
			return {
				...state,
				configuration: { ...state.configuration,
					causeType: action.payload.type,
				},
			};
		case 'SET_COUNTRY':
			await saveSettingWithOnboardingAPI( 'country', action.payload.country );
			const newStatesList = await getStatesListWithOnboardingAPI( action.payload.country );
			const newCurrency = await getCurrencyWithOnboardingAPI( action.payload.country );
			return {
				...state,
				statesList: newStatesList,
				configuration: { ...state.configuration,
					country: action.payload.country,
					currency: newCurrency,
				},
			};
		case 'SET_STATE':
			await saveSettingWithOnboardingAPI( 'state_province', action.payload.state );
			return {
				...state,
				configuration: { ...state.configuration,
					state: action.payload.state,
				},
			};
		case 'SET_CURRENCY':
			await saveSettingWithOnboardingAPI( 'currency', action.payload.currency );
			return {
				...state,
				configuration: { ...state.configuration,
					currency: action.payload.currency,
				},
			};
		case 'SET_FUNDRAISING_NEEDS':
			await saveSettingWithOnboardingAPI( 'fundraising_needs', action.payload.fundraisingNeeds );
			return {
				...state,
				configuration: { ...state.configuration,
					fundraisingNeeds: action.payload.needs,
				},
			};
		default:
			return state;
	}
};

export const reducer = ( state, action ) => {
	switch ( action.type ) {
		case 'GO_TO_STEP':
			return {
				...state,
				currentStep: action.payload.step,
			};
		case 'SET_USER_TYPE':
			return {
				...state,
				configuration: { ...state.configuration,
					userType: action.payload.type,
				},
			};
		case 'SET_CAUSE_TYPE':
			return {
				...state,
				configuration: { ...state.configuration,
					causeType: action.payload.type,
				},
			};
		case 'SET_COUNTRY':
			return {
				...state,
				configuration: { ...state.configuration,
					country: action.payload.country,
				},
			};
		case 'SET_STATE':
			return {
				...state,
				configuration: { ...state.configuration,
					state: action.payload.state,
				},
			};
		case 'SET_CURRENCY':
			return {
				...state,
				configuration: { ...state.configuration,
					currency: action.payload.currency,
				},
			};
		case 'SET_FUNDRAISING_NEEDS':
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

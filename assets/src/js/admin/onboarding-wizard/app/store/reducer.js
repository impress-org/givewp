export const reducer = ( state, action ) => {
	switch ( action.type ) {
		case 'GO_TO_STEP':
			return {
				...state,
				currentStep: action.payload.step,
			};
		default:
			return state;
	}
};

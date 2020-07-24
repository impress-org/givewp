// Dispatch GO_TO_STEP action
export const goToStep = ( step ) => {
	return {
		type: 'GO_TO_STEP',
		payload: {
			step,
		},
	};
};

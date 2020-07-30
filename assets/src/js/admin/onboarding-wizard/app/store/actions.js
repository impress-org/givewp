// Dispatch GO_TO_STEP action
export const goToStep = ( step ) => {
	return {
		type: 'GO_TO_STEP',
		payload: {
			step,
		},
	};
};

// Dispatch SET_USER_TYPE action
export const setUserType = ( type ) => {
	return {
		type: 'SET_USER_TYPE',
		payload: {
			type,
		},
	};
};

// Dispatch SET_CAUSE_TYPE action
export const setCauseType = ( type ) => {
	return {
		type: 'SET_CAUSE_TYPE',
		payload: {
			type,
		},
	};
};

// Dispatch SET_COUNTRY action
export const setCountry = ( country ) => {
	return {
		type: 'SET_COUNTRY',
		payload: {
			country,
		},
	};
};

// Dispatch SET_STATE action
export const setState = ( state ) => {
	return {
		type: 'SET_STATE',
		payload: {
			state,
		},
	};
};

// Dispatch SET_CURRENCY action
export const setCurrency = ( currency ) => {
	return {
		type: 'SET_CURRENCY',
		payload: {
			currency,
		},
	};
};

// Dispatch SET_FUNDRAISING_NEEDS action
export const setFundraisingNeeds = ( needs ) => {
	return {
		type: 'SET_FUNDRAISING_NEEDS',
		payload: {
			needs,
		},
	};
};

// Dispatch SET_FEATURES action
export const setFeatures = ( features ) => {
	return {
		type: 'SET_FEATURES',
		payload: {
			features,
		},
	};
};


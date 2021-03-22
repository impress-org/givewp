export const addTab = ( tab ) => {
	return {
		type: 'ADD_TAB',
		payload: {
			tab,
		},
	};
};

export const setProfile = ( profile ) => {
	return {
		type: 'SET_PROFILE',
		payload: {
			profile,
		},
	};
};

export const setStates = ( states ) => {
	return {
		type: 'SET_STATES',
		payload: {
			states,
		},
	};
};

export const setFetchingStates = ( fetchingStates ) => {
	return {
		type: 'SET_FETCHING_STATES',
		payload: {
			fetchingStates,
		},
	};
};

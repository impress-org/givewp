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

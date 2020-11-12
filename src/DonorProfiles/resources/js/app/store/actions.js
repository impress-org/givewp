export const addTab = ( tab ) => {
	return {
		type: 'ADD_TAB',
		payload: {
			tab,
		},
	};
};

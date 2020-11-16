export const setDonations = ( donations ) => {
	return {
		type: 'SET_DONATIONS',
		payload: {
			donations,
		},
	};
};

export const setQuerying = ( querying ) => {
	return {
		type: 'SET_QUERYING',
		payload: {
			querying,
		},
	};
};


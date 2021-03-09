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

export const setError = ( error ) => {
	return {
		type: 'SET_ERROR',
		payload: {
			error,
		},
	};
};

export const setCount = ( count ) => {
	return {
		type: 'SET_COUNT',
		payload: {
			count,
		},
	};
};

export const setAverage = ( average ) => {
	return {
		type: 'SET_AVERAGE',
		payload: {
			average,
		},
	};
};

export const setRevenue = ( revenue ) => {
	return {
		type: 'SET_REVENUE',
		payload: {
			revenue,
		},
	};
};

export const setCurrency = ( currency ) => {
	return {
		type: 'SET_CURRENCY',
		payload: {
			currency,
		},
	};
};


// Dispatch SET_DATES action
export const setDates = ( startDate, endDate ) => {
	return {
		type: 'SET_DATES',
		payload: {
			startDate,
			endDate,
		},
	};
};

// Dispatch SET_RANGE action
export const setRange = ( range ) => {
	return {
		type: 'SET_RANGE',
		payload: {
			range,
		},
	};
};

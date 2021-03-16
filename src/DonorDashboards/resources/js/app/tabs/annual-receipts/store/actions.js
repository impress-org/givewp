export const setAnnualReceipts = ( annualReceipts ) => {
	return {
		type: 'SET_ANNUAL_RECEIPTS',
		payload: {
			annualReceipts,
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


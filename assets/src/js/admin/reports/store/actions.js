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

// Dispatch false ENABLE_PERIOD_SELCTOR action
export const disablePeriodSelector = () => {
	return {
		type: 'ENABLE_PERIOD_SELECTOR',
		payload: false,
	};
};

// Dispatch SET_GIVE_STATUS action
export const setGiveStatus = ( status ) => {
	return {
		type: 'SET_GIVE_STATUS',
		payload: status,
	};
};

// Dispatch true SET_PAGE_LOADED action
export const setPageLoaded = () => {
	return {
		type: 'SET_PAGE_LOADED',
		payload: true,
	};
};

// Dispatch TOGGLE_SETTINGS_PANEL action
export const toggleSettingsPanel = () => {
	return {
		type: 'TOGGLE_SETTINGS_PANEL',
	};
};

// Dispatch SET_CURRENCY action
export const setCurrency = ( currency ) => {
	return {
		type: 'SET_CURRENCY',
		payload: currency,
	};
};

// Dispatch TOGGLE_TEST_MODE action
export const toggleTestMode = () => {
	return {
		type: 'TOGGLE_TEST_MODE',
	};
};


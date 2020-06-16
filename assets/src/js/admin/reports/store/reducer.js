import moment from 'moment';
import { getWindowData } from '../utils';

export const reducer = ( state, action ) => {
	switch ( action.type ) {
		case 'SET_DATES':
			return {
				...state,
				period: {
					startDate: moment( action.payload.startDate ).hour( 0 ),
					endDate: moment( action.payload.endDate ).hour( 23 ),
					range: 'custom',
				},
			};
		case 'SET_RANGE':
			//determine new startDate based on selected range
			let startDate;
			let endDate = state.period.endDate;
			switch ( action.payload.range ) {
				case 'day':
					endDate = moment( state.period.endDate );
					startDate = moment( endDate ).subtract( 1, 'days' );
					break;
				case 'week':
					startDate = moment( state.period.endDate ).subtract( 7, 'days' );
					break;
				case 'month':
					startDate = moment( state.period.endDate ).subtract( 1, 'months' );
					break;
				case 'year':
					startDate = moment( state.period.endDate ).subtract( 1, 'years' );
					break;
				case 'alltime':
					const allTimeStart = getWindowData( 'allTimeStart' );
					startDate = moment( allTimeStart );
					endDate = moment();
					break;
			}
			return {
				...state,
				period: { ...state.period,
					startDate,
					endDate,
					range: action.payload.range,
				},
			};
		case 'SET_GIVE_STATUS':
			return {
				...state,
				giveStatus: action.payload,
			};
		case 'SET_PAGE_LOADED':
			return {
				...state,
				pageLoaded: action.payload,
			};
		case 'TOGGLE_SETTINGS_PANEL':
			return {
				...state,
				settingsPanelToggled: ! state.settingsPanelToggled,
			};
		case 'SET_CURRENCY':
			return {
				...state,
				currency: action.payload,
			};
		case 'TOGGLE_TEST_MODE':
			return {
				...state,
				testMode: ! state.testMode,
			};
		default:
			return state;
	}
};

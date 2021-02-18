import { getWindowData } from '../utils';

export const initialState = {
	tabs: {},
	profile: getWindowData( 'profile' ) ? getWindowData( 'profile' ) : {},
	id: getWindowData( 'id' ),
	countries: getWindowData( 'countries' ),
	states: getWindowData( 'states' ),
	fetchingStates: false,
};

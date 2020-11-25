import { getWindowData } from '../utils';

export const initialState = {
	tabs: {},
	profile: getWindowData( 'profile' ),
	id: getWindowData( 'id' ),
};

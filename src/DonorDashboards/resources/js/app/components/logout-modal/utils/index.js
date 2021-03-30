import axios from 'axios';
import { getAPIRoot } from '../../../utils';

export const logoutWithAPI = () => {
	return axios.post( getAPIRoot() + 'give-api/v2/donor-dashboard/logout', {} )
		.then( ( response ) => response.data );
};

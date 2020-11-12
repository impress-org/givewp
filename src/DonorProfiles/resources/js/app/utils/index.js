import { addTab } from '../store/actions';

export const registerTab = ( tab ) => {
	const { dispatch } = window.giveDonorProfile.store;
	dispatch( addTab( tab ) );
};

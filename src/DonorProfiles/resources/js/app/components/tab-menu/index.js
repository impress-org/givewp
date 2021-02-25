// Store dependencies
import { useSelector } from 'react-redux';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

// Internal dependencies
import TabLink from '../tab-link';

import { logoutWithAPI } from './utils';

import './style.scss';

const TabMenu = () => {
	const tabsSelector = useSelector( state => state.tabs );
	const tabLinks = Object.values( tabsSelector ).map( ( tab, index ) => {
		return (
			<TabLink slug={ tab.slug } label={ tab.label } icon={ tab.icon } key={ index } />
		);
	} );

	const handleLogout = async() => {
		const { status } = await logoutWithAPI();
		if ( status === 200 ) {
			window.location.reload();
		}
	};

	return (
		<div className="give-donor-profile-tab-menu">
			{ tabLinks }
			<div className="give-donor-profile-logout give-donor-profile-tab-link" onClick={ () => handleLogout() }>
				<FontAwesomeIcon icon="sign-out-alt" /> { __( 'Logout', 'give' ) }
			</div>
		</div>
	);
};
export default TabMenu;

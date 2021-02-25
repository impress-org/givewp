// Store dependencies
import { useState, Fragment } from 'react';
import { useSelector } from 'react-redux';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

// Internal dependencies
import TabLink from '../tab-link';
import LogoutModal from '../logout-modal';

import './style.scss';

const TabMenu = () => {
	const [ logoutModalOpen, setLogoutModalOpen ] = useState( false );

	const tabsSelector = useSelector( state => state.tabs );
	const tabLinks = Object.values( tabsSelector ).map( ( tab, index ) => {
		return (
			<TabLink slug={ tab.slug } label={ tab.label } icon={ tab.icon } key={ index } />
		);
	} );

	return (
		<Fragment>
			{ logoutModalOpen && (
				<LogoutModal onRequestClose={ () => setLogoutModalOpen( false ) } />
			) }
			<div className="give-donor-profile-tab-menu">
				{ tabLinks }
				<div className="give-donor-profile-logout give-donor-profile-tab-link" onClick={ () => setLogoutModalOpen( true ) }>
					<FontAwesomeIcon icon="sign-out-alt" /> { __( 'Logout', 'give' ) }
				</div>
			</div>
		</Fragment>
	);
};
export default TabMenu;

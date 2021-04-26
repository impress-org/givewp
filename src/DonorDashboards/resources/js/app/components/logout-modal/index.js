import Button from '../button';
import { logoutWithAPI, getCleanParentHref } from './utils';

import { __ } from '@wordpress/i18n';
;

import './style.scss';

const LogoutModal = ( { onRequestClose } ) => {
	
	const handleLogout = async() => {
		await logoutWithAPI();
		window.parent.location.href = getCleanParentHref();
	};

	return (
		<div className="give-donor-dashboard-logout-modal">
			<div className="give-donor-dashboard-logout-modal__frame">
				<div className="give-donor-dashboard-logout-modal__header">
					{ __( 'Are you sure you want to logout?', 'give' ) }
				</div>
				<div className="give-donor-dashboard-logout-modal__body">
					<div className="give-donor-dashboard-logout-modal__buttons">
						<Button onClick={ () => handleLogout() }>
							{ __( 'Yes, logout', 'give' ) }
						</Button>
						<a className="give-donor-dashboard-logout-modal__cancel" onClick={ () => onRequestClose() }>
							{ __( 'Nevermind', 'give' ) }
						</a>
					</div>
				</div>
			</div>
			<div className="give-donor-dashboard-logout-modal__bg" onClick={ () => onRequestClose() } />
		</div>
	);
};

export default LogoutModal;

import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useSelector } from 'react-redux';
import { __ } from '@wordpress/i18n'

import './style.scss';

const DonorInfo = () => {
	const { name, addresses, company, sinceLastDonation, sinceCreated, avatarUrl, initials } = useSelector( state => state.profile );
	const address = addresses && addresses.billing ? addresses.billing[ 0 ] : null;

	return (
		<div className="give-donor-dashboard-donor-info">
			<div className="give-donor-dashboard-donor-info__avatar">
				<div className="give-donor-dashboard-donor-info__avatar-container">
					{ avatarUrl ? (
						<img alt="Donor Picture" src={ avatarUrl } />
					) : (
						<span className="give-donor-dashboard-donor-info__avatar-initials">
							{ initials ? initials : <FontAwesomeIcon icon="user" /> }
						</span>
					) }
				</div>
			</div>
			<div className="give-donor-dashboard-donor-info__details">
				{ name && (
					<div className="give-donor-dashboard-donor-info__name">
						{ name }
					</div>
				) }
				{ address && (
					<div className="give-donor-dashboard-donor-info__detail">
						<FontAwesomeIcon icon="map-pin" fixedWidth={ true } /> { address.city }, { address.state.length > 0 ? address.state : address.country }
					</div>
				) }
				{ company && (
					<div className="give-donor-dashboard-donor-info__detail">
						<FontAwesomeIcon icon="building" fixedWidth={ true } /> { company }
					</div>
				) }
				{ sinceLastDonation && (
					<div className="give-donor-dashboard-donor-info__detail">
						<FontAwesomeIcon icon="clock" fixedWidth={ true } /> { sprintf( __( 'Last donated %s ago', 'give' ), sinceLastDonation ) }
					</div>
				) }
				{ sinceCreated && (
					<div className="give-donor-dashboard-donor-info__detail">
						<FontAwesomeIcon icon="heart" fixedWidth={ true } /> { sprintf( __( 'Donor for %s', 'give' ), sinceCreated ) }
					</div>
				) }
			</div>
			<div className="give-donor-dashboard-donor-info__badges">
			</div>
		</div>
	);
};
export default DonorInfo;

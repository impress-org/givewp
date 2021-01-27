import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { useSelector } from 'react-redux';

import Badge from '../badge';

import './style.scss';

const DonorInfo = () => {
	const { name, addresses, company, sinceLastDonation, sinceCreated, avatarUrl, initials } = useSelector( state => state.profile );
	const address = addresses.billing ? addresses.billing[ 0 ] : null;

	return (
		<div className="give-donor-profile-donor-info">
			<div className="give-donor-profile-donor-info__avatar">
				<div className="give-donor-profile-donor-info__avatar-container">
					{ avatarUrl ? (
						<img src={ avatarUrl } />
					) : (
						<span className="give-donor-profile-donor-info__avatar-initials">
							{ initials ? initials : <FontAwesomeIcon icon="user" /> }
						</span>
					) }
				</div>
			</div>
			<div className="give-donor-profile-donor-info__details">
				<div className="give-donor-profile-donor-info__name">
					{ name }
				</div>
				{ address && (
					<div className="give-donor-profile-donor-info__detail">
						<FontAwesomeIcon icon="map-pin" /> { address.city }, { address.state.length > 0 ? address.state : address.country }
					</div>
				) }
				{ company && (
					<div className="give-donor-profile-donor-info__detail">
						<FontAwesomeIcon icon="building" /> { company }
					</div>
				) }
				<div className="give-donor-profile-donor-info__detail">
					<FontAwesomeIcon icon="clock" /> Last donated { sinceLastDonation } ago
				</div>
				<div className="give-donor-profile-donor-info__detail">
					<FontAwesomeIcon icon="heart" /> Donor for { sinceCreated }
				</div>
			</div>
			<div className="give-donor-profile-donor-info__badges">
				<Badge icon="sync" label="Recurring Giver" />
				<Badge icon="user" label="Team Captain" />
				<Badge icon="trophy" label="Top Donor" />
			</div>
		</div>
	);
};
export default DonorInfo;

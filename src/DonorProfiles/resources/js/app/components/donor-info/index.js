import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import Badge from '../badge';

import './style.scss';

const DonorInfo = () => {
	return (
		<div className="give-donor-profile-donor-info">
			<div className="give-donor-profile-donor-info__avatar">
				<div className="give-donor-profile-donor-info__avatar-container">
					<img src="https://cdn.vox-cdn.com/thumbor/ClK0Ing_P9O6kLoQGzbzWleylws=/1400x1050/filters:format(jpeg)/cdn.vox-cdn.com/uploads/chorus_asset/file/19892155/robin_hoo.jpeg" />
				</div>
			</div>
			<div className="give-donor-profile-donor-info__details">
				<div className="give-donor-profile-donor-info__name">
					Mr. Robin Hood
				</div>
				<div className="give-donor-profile-donor-info__detail">
					<FontAwesomeIcon icon="map-pin" /> Sherwood Forest, UK
				</div>
				<div className="give-donor-profile-donor-info__detail">
					<FontAwesomeIcon icon="building" /> Merry Men
				</div>
				<div className="give-donor-profile-donor-info__detail">
					<FontAwesomeIcon icon="clock" /> Last donation 25 days ago
				</div>
				<div className="give-donor-profile-donor-info__detail">
					<FontAwesomeIcon icon="heart" /> Donor for 7 months, 6 days
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

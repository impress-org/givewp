import { Fragment } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import './style.scss';

const DonationReceipt = () => {
	return (
		<Fragment>
			<div className="give-donor-profile-donation-receipt__table">
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="user" /> Donor Name:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						Robin Hood
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="envelope" /> Email Address:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						robin@sherwood.net
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="calendar" /> Donation Date:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						October 21, 2020
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="globe" /> Address:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						12 King John Way <br />
						Sherwood Forest, GB <br />
						UK
					</div>
				</div>
			</div>
			<div className="give-donor-profile-donation-receipt__table">
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Payment Method:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						Credit Card
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Payment Status:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						Pending
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Payment Amount:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						$50
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Processing Fee:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						$5
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row give-donor-profile-donation-receipt__row--footer">
					<div className="give-donor-profile-donation-receipt__detail">
						Donation Total:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						$55
					</div>
				</div>
			</div>
		</Fragment>
	);
};
export default DonationReceipt;

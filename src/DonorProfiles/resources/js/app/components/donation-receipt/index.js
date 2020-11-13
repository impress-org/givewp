import { Fragment } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';

import './style.scss';

const DonationReceipt = ( { donation } ) => {
	if ( donation === undefined ) {
		return null;
	}

	const { donor, payment } = donation;

	return (
		<Fragment>
			<div className="give-donor-profile-donation-receipt__table">
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="user" /> Donor Name:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ `${ donor.first_name } ${ donor.last_name }` }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="envelope" /> Email Address:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ donor.email }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="calendar" /> Donation Date:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.date }
					</div>
				</div>
				{ donor.address && (
					<div className="give-donor-profile-donation-receipt__row">
						<div className="give-donor-profile-donation-receipt__detail">
							<FontAwesomeIcon icon="globe" /> Address:
						</div>
						<div className="give-donor-profile-donation-receipt__value">
							{ donor.address }
						</div>
					</div>
				) }
			</div>
			<div className="give-donor-profile-donation-receipt__table">
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Payment Method:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.method }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Payment Status:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.status }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Payment Amount:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.amount }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						Processing Fee:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.fee }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row give-donor-profile-donation-receipt__row--footer">
					<div className="give-donor-profile-donation-receipt__detail">
						Donation Total:
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.total }
					</div>
				</div>
			</div>
		</Fragment>
	);
};
export default DonationReceipt;

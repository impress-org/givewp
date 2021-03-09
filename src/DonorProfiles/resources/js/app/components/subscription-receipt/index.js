import { Fragment } from 'react';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

const SubscriptionReceipt = ( { subscription } ) => {
	if ( subscription === undefined ) {
		return null;
	}

	const { donor, payment } = subscription;

	return (
		<Fragment>
			<div className="give-donor-profile-donation-receipt__table">
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="user" /> { __( 'Donor Name:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ `${ donor.first_name } ${ donor.last_name }` }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="envelope" /> { __( 'Email Address:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ donor.email }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						<FontAwesomeIcon icon="calendar" /> { __( 'Donation Date:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.date }
					</div>
				</div>
				{ donor.address && (
					<div className="give-donor-profile-donation-receipt__row">
						<div className="give-donor-profile-donation-receipt__detail">
							<FontAwesomeIcon icon="globe" /> { __( 'Address:', 'give' ) }
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
						{ __( 'Payment Method:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.method }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						{ __( 'Subscription Status:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.status.label }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						{ __( 'Payment Amount:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.amount.formatted }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row">
					<div className="give-donor-profile-donation-receipt__detail">
						{ __( 'Processing Fee:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.fee }
					</div>
				</div>
				<div className="give-donor-profile-donation-receipt__row give-donor-profile-donation-receipt__row--footer">
					<div className="give-donor-profile-donation-receipt__detail">
						{ __( 'Donation Total:', 'give' ) }
					</div>
					<div className="give-donor-profile-donation-receipt__value">
						{ payment.total }
					</div>
				</div>
			</div>
		</Fragment>
	);
};
export default SubscriptionReceipt;

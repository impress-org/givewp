import { Link } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

const DonationRow = ( { donation } ) => {
	const { id, form, payment } = donation;

	return (
		<div className="give-donor-profile-table__row">
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-amount">{ payment.amount }</div>
			</div>
			<div className="give-donor-profile-table__column">
				{ form.title }
			</div>
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-date">{ payment.date }</div>
				<div className="give-donor-profile-table__donation-time">{ payment.time }</div>
			</div>
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-status">
					<div className="give-donor-profile-table__donation-status-indicator" style={ { background: payment.status.color } } />
					<div className="give-donor-profile-table__donation-status-label">
						{ payment.status.label }
					</div>
				</div>
				{ payment.mode !== 'live' && (
					<div className="give-donor-profile-table__donation-test-tag">
						{ __( 'Test Donation', 'give' ) }
					</div>
				) }
			</div>
			<div className="give-donor-profile-table__pill">
				<div className="give-donor-profile-table__donation-id">ID: { id }</div>
				<div className="give-donor-profile-table__donation-receipt">
					<Link to={ `/donation-history/${ id }` }>
						{ __( 'View Receipt', 'give' ) } <FontAwesomeIcon icon="arrow-right" />
					</Link>
				</div>
			</div>
		</div>
	);
};

export default DonationRow;

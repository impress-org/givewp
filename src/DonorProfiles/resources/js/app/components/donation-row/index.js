import { Link } from 'react-router-dom';
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
				{ payment.date }
			</div>
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-status">
					{ payment.status }
				</div>
			</div>
			<div className="give-donor-profile-table__pill">
				<div className="give-donor-profile-table__donation-id">ID: { id }</div>
				<div className="give-donor-profile-table__donation-receipt">
					<Link to={ `/donation-history/${ id }` }>
						{ __( 'View Receipt', 'give' ) }
					</Link>
				</div>
			</div>
		</div>
	);
};

export default DonationRow;

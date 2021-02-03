import { Link } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;

const SubscriptionRow = ( { subscription } ) => {
	const id = subscription[ 0 ];
	const { amount, frequency, progress, renewalDate, status, form } = subscription[ 1 ];

	return (
		<div className="give-donor-profile-table__row">
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-amount">{ amount } / { frequency }</div>
				{ form.title }
			</div>
			<div className="give-donor-profile-table__column">
				<div className="give-donor-profile-table__donation-status">
					{ status }
				</div>
			</div>
			<div className="give-donor-profile-table__column">
				{ renewalDate }
			</div>
			<div className="give-donor-profile-table__column">
				{ progress }
			</div>
			<div className="give-donor-profile-table__pill">
				<div className="give-donor-profile-table__donation-receipt">
					<Link to={ `/recurring-donations/${ id }` }>
						{ __( 'Update Payment Method', 'give' ) } <FontAwesomeIcon icon="arrow-right" />
					</Link>
				</div>
				<div className="give-donor-profile-table__donation-receipt">
					<Link to={ `/recurring-donations/${ id }` }>
						{ __( 'View Receipt', 'give' ) } <FontAwesomeIcon icon="arrow-right" />
					</Link>
				</div>
				<div className="give-donor-profile-table__donation-receipt">
					<Link to={ `/recurring-donations/${ id }` }>
						{ __( 'Cancel Subscription', 'give' ) }
					</Link>
				</div>
			</div>
		</div>
	);
};

export default SubscriptionRow;

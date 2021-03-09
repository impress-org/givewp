import { Link } from 'react-router-dom';
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
const { __ } = wp.i18n;
import { useState, Fragment } from 'react';
import SubscriptionCancelModal from '../subscription-cancel-modal';
import { useWindowSize } from '../../hooks';

const SubscriptionRow = ( { subscription } ) => {
	const { id, payment, form, gateway } = subscription;
	const { width } = useWindowSize();

	const [ cancelModalOpen, setCancelModalOpen ] = useState( false );

	return (
		<Fragment>
			{ cancelModalOpen && <SubscriptionCancelModal id={ id } form={ form } onRequestClose={ () => setCancelModalOpen( false ) } /> }
			<div className="give-donor-profile-table__row">
				<div className="give-donor-profile-table__column">
					{ width < 920 && <div className="give-donor-profile-table__mobile-header">{ __( 'Amount', 'give' ) }</div> }
					<div className="give-donor-profile-table__donation-amount">{ payment.amount.formatted } / { payment.frequency }</div>
					{ form.title }
				</div>
				<div className="give-donor-profile-table__column">
					{ width < 920 && <div className="give-donor-profile-table__mobile-header">{ __( 'Status', 'give' ) }</div> }
					<div className="give-donor-profile-table__donation-status">
						{ payment.status.label }
					</div>
				</div>
				<div className="give-donor-profile-table__column">
					{ width < 920 && <div className="give-donor-profile-table__mobile-header">{ __( 'Next Renewal', 'give' ) }</div> }
					{ payment.renewalDate }
				</div>
				<div className="give-donor-profile-table__column">
					{ width < 920 && <div className="give-donor-profile-table__mobile-header">{ __( 'Progress', 'give' ) }</div> }
					{ payment.progress }
				</div>
				<div className="give-donor-profile-table__pill">
					<div className="give-donor-profile-table__donation-receipt">
						<Link to={ `/recurring-donations/receipt/${ id }` }>
							{ __( 'View Receipt', 'give' ) } <FontAwesomeIcon icon="arrow-right" />
						</Link>
					</div>
					{ gateway.can_update && (
						<div className="give-donor-profile-table__donation-receipt">
							<Link to={ `/recurring-donations/manage/${ id }` }>
								{ __( 'Manage Subscription', 'give' ) } <FontAwesomeIcon icon="arrow-right" />
							</Link>
						</div>
					) }
					{ gateway.can_cancel && (
						<div className="give-donor-profile-table__donation-receipt">
							<a onClick={ () => setCancelModalOpen( true ) }>
								{ __( 'Cancel Subscription', 'give' ) }
							</a>
						</div>
					) }
				</div>
			</div>
		</Fragment>
	);
};

export default SubscriptionRow;

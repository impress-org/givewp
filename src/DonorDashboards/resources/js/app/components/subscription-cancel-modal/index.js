import Button from '../button';
import { cancelSubscriptionWithAPI } from './utils';

import { __ } from '@wordpress/i18n';
;

import './style.scss';

const SubscriptionCancelModal = ( { id, onRequestClose } ) => {
	const handleCancel = async() => {
		await cancelSubscriptionWithAPI( id );
		onRequestClose();
	};

	return (
		<div className="give-donor-dashboard-cancel-modal">
			<div className="give-donor-dashboard-cancel-modal__frame">
				<div className="give-donor-dashboard-cancel-modal__header">
					{ __( 'Cancel Subscription?', 'give' ) }
				</div>
				<div className="give-donor-dashboard-cancel-modal__body">
					<div className="give-donor-dashboard-cancel-modal__buttons">
						<Button onClick={ () => handleCancel() }>
							{ __( 'Yes, cancel', 'give' ) }
						</Button>
						<a className="give-donor-dashboard-cancel-modal__cancel" onClick={ () => onRequestClose() }>
							{ __( 'Nevermind', 'give' ) }
						</a>
					</div>
				</div>
			</div>
			<div className="give-donor-dashboard-cancel-modal__bg" onClick={ () => onRequestClose() } />
		</div>
	);
};

export default SubscriptionCancelModal;

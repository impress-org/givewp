import Button from '../button';
import { cancelSubscriptionWithAPI } from './utils';

const { __ } = wp.i18n;

import './style.scss';

const SubscriptionCancelModal = ( { id, onRequestClose } ) => {
	const handleCancel = async() => {
		await cancelSubscriptionWithAPI( id );
		onRequestClose();
	};

	return (
		<div className="give-donor-profile-cancel-modal">
			<div className="give-donor-profile-cancel-modal__frame">
				<div className="give-donor-profile-cancel-modal__header">
					{ __( 'Cancel Subscription?', 'give' ) }
				</div>
				<div className="give-donor-profile-cancel-modal__body">
					<div className="give-donor-profile-cancel-modal__buttons">
						<Button onClick={ () => handleCancel() }>
							{ __( 'Yes, cancel', 'give' ) }
						</Button>
						<a onClick={ () => onRequestClose() }>
							{ __( 'Nevermind', 'give' ) }
						</a>
					</div>
				</div>
			</div>
			<div className="give-donor-profile-cancel-modal__bg" onClick={ () => onRequestClose() } />
		</div>
	);
};

export default SubscriptionCancelModal;

import Button from '../button';
import { cancelSubscriptionWithAPI } from './utils';

import './style.scss';

const SubscriptionCancelModal = ( { id, form, onRequestClose } ) => {
	const handleCancel = async() => {
		await cancelSubscriptionWithAPI( id );
		onRequestClose();
	};

	return (
		<div className="give-donor-profile-cancel-modal">
			<div className="give-donor-profile-cancel-modal__frame">
				<div className="give-donor-profile-cancel-modal__header">
					Cancel Subscription?
				</div>
				<div className="give-donor-profile-cancel-modal__body">
					{ form.title }
					<div className="give-donor-profile-cancel-modal__buttons">
						<Button onClick={ () => handleCancel() }>
							Yes, cancel.
						</Button>
						<a onClick={ () => onRequestClose() }>
							Nevermind
						</a>
					</div>
				</div>
			</div>
			<div className="give-donor-profile-cancel-modal__bg" onClick={ () => onRequestClose() } />
		</div>
	);
};

export default SubscriptionCancelModal;

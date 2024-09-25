import {__} from '@wordpress/i18n';
import Button from '../button';

import './style.scss';

const SubscriptionCancel = ({onRequestClose, handleCancel, cancelling}) => {
    return (
        <div className="give-donor-dashboard-cancel-modal__buttons">
            <Button disabled={cancelling} onClick={() => handleCancel()}>
                {!cancelling ? __('Yes, cancel', 'give') : __('Cancelling...', 'give')}
            </Button>
            <a className="give-donor-dashboard-cancel-modal__cancel" onClick={() => onRequestClose()}>
                {__('Nevermind', 'give')}
            </a>
        </div>
    );
};

export default SubscriptionCancel;

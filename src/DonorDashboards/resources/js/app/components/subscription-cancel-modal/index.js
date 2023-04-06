import Button from '../button';
import {cancelSubscriptionWithAPI} from './utils';

import {__} from '@wordpress/i18n';
import './style.scss';
import {useState} from 'react';

const responseIsError = (response) => {
    return response?.data?.code.includes('error');
};

const getErrorMessageFromResponse = (response) => {
    if (response?.data?.code === 'internal_server_error' || !response?.data?.message) {
        return __('An error occurred while cancelling your subscription.', 'give');
    }

    return response?.data?.message;
};

const SubscriptionCancelModal = ({id, onRequestClose}) => {
    const [cancelling, setCancelling] = useState(false);
    const handleCancel = async () => {
        setCancelling(true);
        const response = await cancelSubscriptionWithAPI(id);

        if (responseIsError(response)) {
            const errorMessage = getErrorMessageFromResponse(response);
            
            window.alert(errorMessage);
        }

        setCancelling(false);

        onRequestClose();
    };

    return (
        <div className="give-donor-dashboard-cancel-modal">
            <div className="give-donor-dashboard-cancel-modal__frame">
                <div className="give-donor-dashboard-cancel-modal__header">{__('Cancel Subscription?', 'give')}</div>
                <div className="give-donor-dashboard-cancel-modal__body">
                    <div className="give-donor-dashboard-cancel-modal__buttons">
                        <Button disabled={cancelling} onClick={() => handleCancel()}>
                            {!cancelling ? __('Yes, cancel', 'give') : __('Cancelling...', 'give')}
                        </Button>
                        <a className="give-donor-dashboard-cancel-modal__cancel" onClick={() => onRequestClose()}>
                            {__('Nevermind', 'give')}
                        </a>
                    </div>
                </div>
            </div>
            <div className="give-donor-dashboard-cancel-modal__bg" onClick={() => onRequestClose()} />
        </div>
    );
};

export default SubscriptionCancelModal;

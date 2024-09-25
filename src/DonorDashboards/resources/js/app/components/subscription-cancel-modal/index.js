import {__} from '@wordpress/i18n';
import {useState} from 'react';
import Button from '../button';
import {cancelSubscriptionWithAPI} from './utils';
import DashboardLoadingSpinner from '../dashboard-loading-spinner';
import './style.scss';

const SubscriptionCancel = ({id, onRequestClose}) => {
    const [cancelling, setCancelling] = useState(false);

    const handleCancel = async () => {
        setCancelling(true);
        await cancelSubscriptionWithAPI(id);
        onRequestClose();
        setCancelling(false);
    };

    return (
        <div style={{position: 'relative'}}>
            {cancelling && <DashboardLoadingSpinner />}
            <div className="give-donor-dashboard-cancel-modal__buttons">
                <Button disabled={cancelling} onClick={() => handleCancel()}>
                    {!cancelling ? __('Yes, cancel', 'give') : __('Cancelling...', 'give')}
                </Button>
                <a className="give-donor-dashboard-cancel-modal__cancel" onClick={() => onRequestClose()}>
                    {__('Nevermind', 'give')}
                </a>
            </div>
            <div className="give-donor-dashboard-cancel-modal__bg" onClick={() => onRequestClose()} />
        </div>
    );
};

export default SubscriptionCancel;

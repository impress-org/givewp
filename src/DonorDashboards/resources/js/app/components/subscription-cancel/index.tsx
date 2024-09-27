import {__} from '@wordpress/i18n';
import Button from '../button';
import cx from 'classnames';

import './style.scss';

type SubscriptionCancelProps = {
    showPausingControls: boolean;
    closeModal: () => void;
    handlePauseRequest: () => void;
    handleCancel: () => void;
    cancelling: boolean;
    subscription: {
        id: string;
        gateway: {
            can_pause: boolean;
        };
        payment: {
            frequency: string;
        };
    };
};

const SubscriptionCancel = ({
    showPausingControls,
    handlePauseRequest,
    handleCancel,
    cancelling,
    closeModal,
}: SubscriptionCancelProps) => {
    return (
        <div className="give-donor-dashboard-cancel-modal__buttons">
            <div className={cx('give-donor-dashboard-cancel-modal__buttons-wrapper')}>
                <Button disabled={cancelling} onClick={() => handleCancel()}>
                    {!cancelling ? __('Yes, cancel', 'give') : __('Cancelling...', 'give')}
                </Button>
                {showPausingControls && (
                    <Button onClick={handlePauseRequest} variant>
                        {__('Pause', 'give')}
                    </Button>
                )}
            </div>
            <a
                className={cx('give-donor-dashboard-cancel-modal__cancel', {
                    ['give-donor-dashboard-cancel-modal__cancel--pause']: showPausingControls,
                })}
                onClick={closeModal}
            >
                {__('Nevermind', 'give')}
            </a>
        </div>
    );
};

export default SubscriptionCancel;

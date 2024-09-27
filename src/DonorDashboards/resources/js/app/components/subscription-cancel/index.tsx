import {__} from '@wordpress/i18n';
import Button from '../button';
import cx from 'classnames';

import './style.scss';

type SubscriptionCancelProps = {
    exitCancelModal: any;
    handlePauseRequest: any;
    handleCancel: any;
    cancelling: any;
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
    handlePauseRequest,
    handleCancel,
    cancelling,
    subscription,
    exitCancelModal,
}: SubscriptionCancelProps) => {
    const showPausingControls =
        subscription.gateway.can_pause && !['Quarterly', 'Yearly'].includes(subscription.payment.frequency);

    return (
        <div className="give-donor-dashboard-cancel-modal__buttons">
            <div>
                <Button disabled={cancelling} onClick={() => handleCancel()}>
                    {!cancelling ? __('Yes, cancel', 'give') : __('Cancelling...', 'give')}
                </Button>
                {showPausingControls && (
                    <Button onClick={() => handlePauseRequest} variant>
                        {__('Pause', 'give')}
                    </Button>
                )}
            </div>
            <a
                className={cx('give-donor-dashboard-cancel-modal__cancel', {
                    ['give-donor-dashboard-cancel-modal__cancel--pause']: showPausingControls,
                })}
                onClick={exitCancelModal}
            >
                {__('Nevermind', 'give')}
            </a>
        </div>
    );
};

export default SubscriptionCancel;

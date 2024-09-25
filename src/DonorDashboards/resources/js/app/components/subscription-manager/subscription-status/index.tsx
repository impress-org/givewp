import React from 'react';
import cx from 'classnames';
import {__} from '@wordpress/i18n';

import './style.scss';

export default function SubscriptionStatus({subscription}) {
    const isActive = subscription.payment.status.id === 'active';

    return (
        <div
            className={cx('givewp-dashboard-subscription-status', {
                ['givewp-dashboard-subscription-status--active']: isActive,
                ['givewp-dashboard-subscription-status--paused']: !isActive,
            })}
        >
            {isActive ? __('Active', 'give') : __('Paused', 'give')}
        </div>
    );
}

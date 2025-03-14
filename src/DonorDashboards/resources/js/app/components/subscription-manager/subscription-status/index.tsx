import React from 'react';
import cx from 'classnames';
import {__} from '@wordpress/i18n';

import './style.scss';

export default function SubscriptionStatus({subscription}) {
    const status = subscription.payment.status.id;
    const label = subscription.payment.status.label;

    return (
        <div className={`givewp-dashboard-subscription-status givewp-dashboard-subscription-status--${status}`}>
            {label}
        </div>
    );
}

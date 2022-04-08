import {__} from "@wordpress/i18n";
import cx from "classnames";

import styles from './DonorType.module.scss';
import RenewalIcon from "@givewp/components/ListTable/images/RenewalIcon";
import RecurringIcon from "@givewp/components/ListTable/images/RecurringIcon";
import {useUniqueId} from "@givewp/components/ListTable/hooks/useUniqueId";

interface DonorTypeProps {
    type: 'single'|'repeat'|'subscriber'|'new';
}

const donorTypeConfig = {
    single: {
        badgeStyle: styles.oneTime,
        badgeContent: __('1x', 'give'),
        label: __('one-time donor', 'give'),
    },
    repeat: {
        badgeStyle: styles.repeat,
        badgeContent: RenewalIcon,
        label: __('repeat donor', 'give'),
    },
    subscriber: {
        badgeStyle: styles.subscriber,
        badgeContent: RecurringIcon,
        label: __('subscriber', 'give'),
    },
    new: {
        badgeStyle: styles.new,
        badgeContent: '-',
        label: __('no donations', 'give'),
    }
}

const donationTypeConfig = {
    single: {
        badgeStyle: styles.oneTime,
        badgeContent: __('1x', 'give'),
        label: __('one-time', 'give'),
    },
    renewal: {
        badgeStyle: styles.repeat,
        badgeContent: RenewalIcon,
        label: __('renewal', 'give'),
    },
    subscription: {
        badgeStyle: styles.subscriber,
        badgeContent: RecurringIcon,
        label: __('new subscriber', 'give'),
    }
}

function TypeBadge ({config}) {
    if(!config) return null;
    // we need a unique element ID for aria labelling
    const badgeId = useUniqueId('giveDonationTypeBadge-');
    return (
        <>
            <div className={styles.container}>
                {
                    typeof config.badgeContent === 'string' ?
                    <div role='img' aria-labelledby={badgeId} className={cx(styles.badge, config.badgeStyle)}>
                        {config.badgeContent}
                    </div>
                    :
                    <config.badgeContent aria-labelledby={badgeId} className={cx(styles.badge, config.badgeStyle)}/>
                }
                <label id={badgeId} className={styles.label}>{config.label}</label>
            </div>
        </>
    );
}

export function DonorType ({type}: DonorTypeProps) {
    return <TypeBadge config={donorTypeConfig[type]}/>;
}

export function DonationType ({type}) {
    return <TypeBadge config={donationTypeConfig[type]}/>;
}

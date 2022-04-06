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
        badgeContent: '',
        label: __('new donor', 'give'),
    }
}

export default function DonorType ({type}: DonorTypeProps) {
    const typeConfig = donorTypeConfig[type];
    const badgeId = useUniqueId('giveDonationTypeBadge-');
    return (
        <div className={styles.container}>
            {
                typeof typeConfig.badgeContent === 'string' ?
                <div role='img' aria-labelledby={badgeId} className={cx(styles.badge, typeConfig.badgeStyle)}>
                    {typeConfig.badgeContent}
                </div>
                :
                <typeConfig.badgeContent aria-labelledby={badgeId} className={cx(styles.badge, typeConfig.badgeStyle)}/>
            }
            <label id={badgeId} className={styles.label}>{donorTypeConfig[type].label}</label>
        </div>
    );
}

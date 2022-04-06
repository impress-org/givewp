import {__} from "@wordpress/i18n";
import cx from "classnames";

import styles from './DonorType.module.scss';
import Renewal from "@givewp/components/ListTable/images/Renewal";

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
        badgeContent: Renewal,
        label: __('repeat donor', 'give'),
    },
    subscriber: {
        badgeStyle: styles.subscriber,
        badgeContent: '',
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
    return (
        <div className={styles.container}>
            {typeof typeConfig.badgeContent === 'string' ?
                <div className={cx(styles.badge, typeConfig.badgeStyle)}>
                    {typeConfig.badgeContent}
                </div>
                :
                <typeConfig.badgeContent className={cx(styles.badge, typeConfig.badgeStyle)}/>
            }
            <label className={styles.label}>{donorTypeConfig[type].label}</label>
        </div>
    );
}

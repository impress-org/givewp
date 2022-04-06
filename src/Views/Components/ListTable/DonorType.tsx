import styles from './DonorType.module.scss';
import {__} from "@wordpress/i18n";
import cx from "classnames";

interface DonorTypeProps {
    type: 'single'|'recurring'|'subscriber'|'new';
}

export default function DonorType ({type}: DonorTypeProps) {
    return (
        <div className={styles.container}>
            <div className={cx(styles.badge, styles.oneTime)}>{__('1x', 'give')}</div>
            <label className={styles.label}>{__('one-time donor', 'give')}</label>
        </div>
    );
}

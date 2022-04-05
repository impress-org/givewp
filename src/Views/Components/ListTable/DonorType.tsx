import styles from './DonorType.module.scss';
import {__} from "@wordpress/i18n";

interface DonorTypeProps {
    type: 'single'|'recurring'|'subscriber'|'new';
}

export default function DonorType ({type}: DonorTypeProps) {
    return (
        <div className={styles.badge}>
            {__('one-time donor', 'give')}
        </div>
    );
}

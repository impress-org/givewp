import PaypalIcon from '@givewp/components/AdminUI/Icons/PaypalIcon';

import {DonationTypeProps} from './types';

import styles from './style.module.scss';

function renderPaymentTypeIcon(type: any) {
    switch (type) {
        case 'single':
            return <PaypalIcon />;
        case 'renewal':
            return <span>stripe test</span>;
        case 'subscription':
            return <span>{}</span>;
        default:
            return '';
    }
}

export default function DonationType({donationType}: DonationTypeProps) {
    return <div className={styles.paymentType}>{donationType}</div>;
}

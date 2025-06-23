import React from 'react';
import styles from './styles.module.scss';
import DonationStats from './DonationStats';
import DonationSummaryGrid from './DonationSummaryGrid';
import DonationDetailedReceipt from './DonationDetailedReceipt';
import { useDonationEntityRecord } from '@givewp/donations/utils';

/**
 * @unreleased
 */
export default function DonationDetailsPageOverviewTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const donationId = parseInt(urlParams.get('id') ?? '0');

    return (
        <div className={styles.overview}>
            <DonationStats donationId={donationId} />

            <div className={styles.left}>
                <DonationSummaryGrid  />
             </div>

            <div className={styles.right}>
                <DonationDetailedReceipt donationId={donationId} />
            </div>

        </div>
    );
}
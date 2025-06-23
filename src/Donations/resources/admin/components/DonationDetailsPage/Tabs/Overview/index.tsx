import React from 'react';
import styles from './styles.module.scss';
import DonationStats from './DonationStats';
import DonationSummaryGrid from './DonationSummaryGrid';
import DonationDetailedReceipt from './DonationDetailedReceipt';
import { useDonationStatistics } from '@givewp/donations/hooks/useDonationStatistics';
import { useDonationEntityRecord } from '@givewp/donations/utils';

/**
 * @unreleased
 */
export default function DonationDetailsPageOverviewTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const donationId = parseInt(urlParams.get('id') ?? '0');
    const { statistics, hasResolved, isResolving } = useDonationStatistics(donationId);
    const { record: donation } = useDonationEntityRecord();

    if (!hasResolved || !statistics) {
        return null;
    }

    console.log(statistics);

    return (
        <div className={styles.overview}>
            <DonationStats
                amount={statistics.donation.amount}
                isResolving={isResolving}
                feeAmountRecovered={statistics.donation.feeAmountRecovered}
            />

            <div className={styles.left}>
                <DonationSummaryGrid
                    campaignTitle={statistics.campaign.title}
                    donorName={statistics.donor.name}
                    donorEmail={statistics.donor.email}
                    gatewayId={statistics.donation.paymentMethod}
                    donationDate={statistics.donation.date}
                    donationType={donation?.type}
                    donorId={statistics.donor.id}
                    campaignId={statistics.campaign.id}
                />
             </div>

            <div className={styles.right}>
                <DonationDetailedReceipt donationId={donationId} />
            </div>

        </div>
    );
}
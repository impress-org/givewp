import styles from './styles.module.scss';
import DonationStats from './DonationStats';
import DonationSummaryGrid from './DonationSummaryGrid';
import { useDonationStatistics } from '@givewp/donations/hooks/useDonationStatistics';
import { useDonationEntityRecord } from '@givewp/donations/utils';
import {DonationNotes, DonorNotes} from '@givewp/src/Admin/components/PrivateNotes';

/**
 * @unreleased
 */
export default function DonationDetailsPageOverviewTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const donationId = parseInt(urlParams.get('id') ?? '0');
    const { statistics, hasResolved, isResolving } = useDonationStatistics(donationId);
    const { record: donation, hasResolved: hasResolvedDonation } = useDonationEntityRecord();

    if (!hasResolved || !hasResolvedDonation) {
        return null;
    }

    return (
        <>
            <div className={styles.overview}>
                <DonationStats
                    donation={statistics.donation}
                    details={statistics.receipt?.donationDetails}
                    isResolving={isResolving}
                />
                <div className={styles.left}>
                    <DonationSummaryGrid
                        campaign={statistics.campaign}
                        donor={statistics.donor}
                        donation={statistics.donation}
                        details={statistics.receipt?.donationDetails}
                        donationType={donation?.type}
                        subscriptionId={donation?.subscriptionId}
                    />

                    <div className={styles.card}>
                        <DonationNotes donationId={donationId} />
                    </div>
                </div>
            </div>
        </>
    );
}

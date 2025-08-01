import styles from './styles.module.scss';
import DonationStats from './DonationStats';
import DonationSummaryGrid from './DonationSummaryGrid';
import DonationReceipt from './DonationReceipt';
import { useDonationEntityRecord } from '@givewp/donations/utils';
import {DonationNotes} from '@givewp/src/Admin/components/PrivateNotes';
import Spinner from '@givewp/src/Admin/components/Spinner';

/**
 * @since 4.6.0
 */
export default function DonationDetailsPageOverviewTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const donationId = parseInt(urlParams.get('id') ?? '0');
    const {record: donation, hasResolved: hasResolvedDonation, isResolving: isResolvingDonation } = useDonationEntityRecord(donationId);

    if (!hasResolvedDonation || isResolvingDonation || !donation) {
        // TODO: Add loading state
        return <Spinner />;
    }

    return (
        <div className={styles.overview}>
            <DonationStats donation={donation} isResolving={isResolvingDonation} />

            <div className={styles.left}>
                <DonationSummaryGrid
                    donation={donation}
                />
                <div className={styles.card}>
                    <DonationNotes donationId={donationId} />
                </div>
            </div>

            <div className={styles.right}>
                <DonationReceipt donation={donation} />
            </div>
        </div>
    );
}

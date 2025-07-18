import React from 'react';
import DonorTransactions from './DonorTransactions';
import DonorSummary from './DonorSummary';
import DonorStats from './DonorStats';
import DonorContributions from './DonorContributions';
import DonorPrivateNotes from './DonorPrivateNotes';
import NotificationPlaceholder from '@givewp/components/AdminDetailsPage/Notifications';
import styles from './styles.module.scss';

/**
 * @since 4.5.0
 */
export default function DonorDetailsPageOverviewTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const donorId = parseInt(urlParams.get('id') ?? '0');

    return (
        <div className={styles.overview}>
            <DonorStats donorId={donorId} />

            <div className={styles.left}>
                <DonorContributions donorId={donorId} />
                <DonorTransactions donorId={donorId} />
                <DonorPrivateNotes donorId={donorId} />
             </div>

            <div className={styles.right}>
                <DonorSummary donorId={donorId} />
            </div>

            <NotificationPlaceholder type="snackbar" />
        </div>
    );
}

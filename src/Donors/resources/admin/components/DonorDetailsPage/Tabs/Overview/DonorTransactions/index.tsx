import React from 'react';
import {__} from '@wordpress/i18n';
import classnames from 'classnames';
import Header from '@givewp/src/Admin/components/Header';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import {formatTimestamp} from '@givewp/src/Admin/utils';
import {amountFormatter} from '@givewp/src/Admin/utils';
import {getDonorOptionsWindowData} from '@givewp/donors/utils';
import {useDonorDonations} from '@givewp/donors/hooks/useDonorDonations';

import styles from './styles.module.scss';

/**
 * @since 4.5.0
 */
export type Transaction = {
    campaign: string;
    status: 'Completed' | 'Pending' | 'Failed' | 'Refunded';
    timestamp: string;
    amount: string;
};

/**
 * @since 4.5.0
 */
const statusMap: Record<string, 'Completed' | 'Failed' | 'Pending' | 'Refunded'> = {
    publish: 'Completed',
    completed: 'Completed',
    processing: 'Pending',
    pending: 'Pending',
    failed: 'Failed',
    cancelled: 'Failed',
    abandoned: 'Failed',
    preapproval: 'Pending',
    revoked: 'Failed',
    refunded: 'Refunded',
};

/**
 * @since 4.5.0
 */
interface DonorTransactionsProps {
    donorId: number;
}

/**
 * @since 4.5.0
 */
export default function DonorTransactions({donorId}: DonorTransactionsProps) {
    const {mode} = getDonorOptionsWindowData();
    const {donations} = useDonorDonations({donorId, mode});
    const donationsListUrl = `admin.php?page=give-payment-history&donor=${donorId}`;

    const transactions: Transaction[] = !donations
        ? []
        : donations.map((donation) => ({
            campaign: donation.formTitle,
            status: statusMap[donation.status] || 'Pending',
            timestamp: donation.createdAt.date,
            amount: amountFormatter(donation.amount.currency).format(parseFloat(donation.amount.value)),
        }));

    return (
        <OverviewPanel>
            <Header
                title={__('Recent Transactions', 'give')}
                subtitle={__('Shows the five recent transactions', 'give')}
                href={donationsListUrl}
                actionText={__('View All Transactions', 'give')}
            />
            <div className={styles.transactionList}>
                {transactions.map(({status, campaign, timestamp, amount}, index) => (
                    <div key={index} className={styles.transactionItem}>
                        <div className={styles.transactionIcon}>
                            <span className={styles.timeline} />
                            <div className={styles.svgContainer}>
                                <svg
                                    width="24"
                                    height="24"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    xmlns="http://www.w3.org/2000/svg"
                                >
                                    <path
                                        d="M12 6v6l4 2m6-2c0 5.523-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2s10 4.477 10 10z"
                                        stroke="#4B5563"
                                        strokeWidth="2"
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                    />
                                </svg>
                            </div>
                        </div>

                        <div className={styles.transactionInfo}>
                            <div className={styles.campaign}>
                                <span>
                                    Donated to <strong>{campaign}</strong> Campaign.
                                </span>
                                <span className={classnames(styles.status, styles[status.toLowerCase()])}>
                                    {status}
                                </span>
                            </div>
                            <span className={styles.timestamp}>{formatTimestamp(timestamp)}</span>
                        </div>
                        <div className={styles.amount}>{amount}</div>
                    </div>
                ))}
            </div>
        </OverviewPanel>
    );
}

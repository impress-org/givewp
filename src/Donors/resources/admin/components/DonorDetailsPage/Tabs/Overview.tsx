import React, {useState} from 'react';
import {__} from '@wordpress/i18n';
import {dateI18n} from '@wordpress/date';
import classnames from 'classnames';
import StatWidget from '@givewp/src/Admin/components/StatWidget';
import Header from '@givewp/src/Admin/components/Header';
import PrivateNotes from '@givewp/src/Admin/components/PrivateNotes';
import TimeSeriesChart from '@givewp/src/Admin/components/Charts/TimeSeriesChart';
import {useDonorStatistics} from '@givewp/donors/hooks/useDonorStatistics';
import {amountFormatter, formatTimestamp, getRelativeTimeString} from '@givewp/src/Admin/utils';
import {getDonorOptionsWindowData, useDonorEntityRecord} from '@givewp/donors/utils';
import {useDonorDonations} from '@givewp/donors/hooks/useDonorDonations';

import styles from '@givewp/donors/admin/components/DonorDetailsPage/DonorDetailsPage.module.scss';
import NotificationPlaceholder from '@givewp/components/AdminDetailsPage/Notifications';

/**
 * @since 4.4.0
 */
type Transaction = {
    campaign: string;
    status: 'Completed' | 'Pending' | 'Failed' | 'Refunded';
    timestamp: string;
    amount: string;
};

/**
 * @since 4.4.0
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
 * @since 4.4.0
 */
export default function DonorDetailsPageOverviewTab() {
    const urlParams = new URLSearchParams(window.location.search);
    const donorId = parseInt(urlParams.get('id') ?? '0');
    const {currency, mode} = getDonorOptionsWindowData();
    const {statistics: stats, isResolving: statsLoading, hasResolved: statsResolved,} = useDonorStatistics(donorId, mode);
    const {donations} = useDonorDonations({donorId, mode});
    const {record: donor} = useDonorEntityRecord(donorId);
    const donationChartEndpoint = `givewp/v3/donations?mode=${mode}&donorId=${donorId}`;
    const donationsListUrl = `admin.php?page=give-payment-history&donor=${donorId}`;

    const transactions: Transaction[] = !donations
        ? []
        : donations.map((donation) => ({
            campaign: donation.formTitle,
            status: statusMap[donation.status] || 'Pending',
            timestamp: donation.createdAt.date,
            amount: amountFormatter(donation.amount.currency).format(parseFloat(donation.amount.value)),
        }));

    const summaryItems = !stats
        ? []
        : [
              {
                  label: __('Donor Since', 'give'),
                  value: dateI18n('M j, Y', donor?.createdAt.date, undefined),
              },
              {
                  label: __('Last Contributed', 'give'),
                  value: stats.donations.last
                      ? getRelativeTimeString(new Date(stats.donations.last.date))
                      : __('Never', 'give'),
              },
              {
                  label: __('First Contribution', 'give'),
                  value: stats.donations.first
                      ? {
                            value1: amountFormatter(currency).format(parseFloat(stats.donations.first.amount)),
                            value2: dateI18n('M j, Y', stats.donations.first.date, undefined),
                        }
                      : __('None', 'give'),
              },
              {
                  label: __('Donor Type', 'give'),
                  value: stats.donorType,
                  isPill: true,
              },
              {
                  label: __('Total Donations', 'give'),
                  value: stats.donations.count?.toString() ?? '0',
              },
              {
                  label: __('Preferred Method', 'give'),
                  value: stats.preferredPaymentMethod || __('None', 'give'),
              },
          ];


    return (
        <div className={styles.grid}>
            <StatWidget
                label={__('Lifetime donations', 'give')}
                value={stats?.donations?.lifetimeAmount ?? 0}
                formatter={amountFormatter(currency)}
                loading={statsLoading || !statsResolved}
            />
            <StatWidget
                label={__('Highest donation', 'give')}
                value={stats?.donations?.highestAmount ?? 0}
                formatter={amountFormatter(currency)}
                loading={statsLoading || !statsResolved}
            />
            <StatWidget
                label={__('Average donation', 'give')}
                value={stats?.donations?.averageAmount ?? 0}
                formatter={amountFormatter(currency)}
                loading={statsLoading || !statsResolved}
            />

            <div className={styles.leftColumn}>
                <div className={classnames(styles.card, styles.contributionsCard)}>
                    <Header
                        title={__('Contributions', 'give')}
                        subtitle={__("Shows the donor's contribution over time", 'give')}
                    />
                    <TimeSeriesChart
                        title={__('Contributions', 'give')}
                        endpoint={donationChartEndpoint}
                        amountFormatter={amountFormatter(currency)}
                    />
                </div>

                <div className={styles.card}>
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
                </div>

                <div className={styles.card}>
                    <PrivateNotes donorId={donorId} />
                </div>
            </div>

            <div className={styles.rightColumn}>
                <div className={classnames(styles.card, styles.summaryCard)}>
                    <Header
                        title={__('Summary', 'give')}
                        subtitle={__('Additional information about the donor', 'give')}
                    />
                    {summaryItems.map((item, index) => (
                        <div className={styles.summaryCard} key={index}>
                            <p className={styles.summaryCardLabel}>{item.label}</p>
                            {typeof item.value === 'object' ? (
                                <div className={styles.summaryCardValues}>
                                    <p>{item.value.value1}</p>
                                    <p>{item.value.value2}</p>
                                </div>
                            ) : (
                                <strong className={classnames(styles.summaryCardValue, {[styles.pill]: item.isPill})}>
                                    {item.value}
                                </strong>
                            )}
                        </div>
                    ))}
                </div>
            </div>

            <NotificationPlaceholder type="snackbar" />
        </div>
    );
}

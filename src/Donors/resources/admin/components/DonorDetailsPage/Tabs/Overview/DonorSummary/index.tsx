import React from 'react';
import {__} from '@wordpress/i18n';
import {dateI18n} from '@wordpress/date';
import classnames from 'classnames';
import Header from '@givewp/src/Admin/components/Header';
import OverviewPanel from '@givewp/src/Admin/components/OverviewPanel';
import {amountFormatter, getRelativeTimeString} from '@givewp/src/Admin/utils';
import {useDonorStatistics} from '@givewp/donors/hooks/useDonorStatistics';
import {getDonorOptionsWindowData, useDonorEntityRecord} from '@givewp/donors/utils';

import styles from './styles.module.scss';

/**
 * @since 4.5.0
 */
export type SummaryItem = {
    label: string;
    value: string | {
        value1: string;
        value2: string;
    };
    isPill?: boolean;
};

/**
 * @since 4.5.0
 */
interface SummaryProps {
    donorId: number;
}

/**
 * @since 4.5.0
 */
export default function Summary({donorId}: SummaryProps) {
    const {currency, mode} = getDonorOptionsWindowData();
    const {statistics: stats, isResolving: statsLoading, hasResolved: statsResolved} = useDonorStatistics(donorId, mode);
    const {record: donor} = useDonorEntityRecord(donorId);

    const summaryItems: SummaryItem[] = !stats
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
        <OverviewPanel className={styles.summaryCard}>
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
        </OverviewPanel>
    );
}

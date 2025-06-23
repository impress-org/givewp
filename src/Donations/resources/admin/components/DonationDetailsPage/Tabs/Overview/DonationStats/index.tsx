import React from 'react';
import {__} from '@wordpress/i18n';
import classnames from 'classnames';
import StatWidget from '@givewp/src/Admin/components/StatWidget';
import {amountFormatter} from '@givewp/src/Admin/utils';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';
import styles from './styles.module.scss';
import { useDonationStatistics } from '@givewp/donations/hooks/useDonationStatistics';

/**
 * @unreleased
 */
interface DonationStatsProps {
    donationId: number;
    mode?: 'live' | 'test';
    campaignId?: number;
}

/**
 * @unreleased
 */
export default function DonationStats({ donationId, mode = 'live', campaignId = 0 }: DonationStatsProps) {
    const { statistics, hasResolved, isResolving } = useDonationStatistics(donationId, mode, campaignId);
    const { eventTicketsEnabled, adminUrl, isFeeRecoveryEnabled, currency } = getDonationOptionsWindowData();

    if (!hasResolved || !statistics) {
        return null;
    }

    return (
        <div className={styles.container}>
            <StatWidget
                label={__('Donation amount', 'give')}
                value={parseFloat(statistics.donation.amount) || 0}
                formatter={amountFormatter(currency)}
                loading={isResolving}
            />
            {eventTicketsEnabled && (
                <StatWidget
                    label={__('Event Ticket', 'give')}
                    value={0}
                    formatter={amountFormatter(currency)}
                    loading={isResolving}
                />
            )}
            <StatWidget
                label={__('Fees recovered', 'give')}
                value={statistics.donation.feeAmountRecovered ? parseFloat(String(statistics.donation.feeAmountRecovered)) : 0}
                formatter={amountFormatter(currency)}
                loading={isResolving}
                href={`${adminUrl}edit.php?post_type=give_forms&page=give-settings&tab=gateways`}
                inActive={!isFeeRecoveryEnabled}
            />
        </div>
    );
} 
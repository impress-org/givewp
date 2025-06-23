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
    amount: string;
    isResolving: boolean;
    feeAmountRecovered: string | number;
    eventTicketAmount?: string | number;
}

/**
 * @unreleased
 */
export default function DonationStats({ amount, isResolving, feeAmountRecovered, eventTicketAmount }: DonationStatsProps) {
    const { eventTicketsEnabled, adminUrl, isFeeRecoveryEnabled, currency } = getDonationOptionsWindowData();

    const parsedEventTicketAmount = typeof eventTicketAmount === 'string'
        ? parseFloat(eventTicketAmount.replace(/[^0-9.-]+/g, ''))
        : eventTicketAmount || 0;

    return (
        <div className={styles.container}>
            <StatWidget
                label={__('Donation amount', 'give')}
                value={parseFloat(amount) || 0}
                formatter={amountFormatter(currency)}
                loading={isResolving}
            />
            {eventTicketsEnabled && (
                <StatWidget
                    label={__('Event Ticket', 'give')}
                    value={parsedEventTicketAmount}
                    formatter={amountFormatter(currency)}
                    loading={isResolving}
                />
            )}
            <StatWidget
                label={__('Fees recovered', 'give')}
                value={typeof feeAmountRecovered === 'string' ? parseFloat(feeAmountRecovered) : feeAmountRecovered}
                formatter={amountFormatter(currency)}
                loading={isResolving}
                href={'https://givewp.com/addons/fee-recovery/'}
                inActive={!isFeeRecoveryEnabled}
            />
        </div>
    );
} 
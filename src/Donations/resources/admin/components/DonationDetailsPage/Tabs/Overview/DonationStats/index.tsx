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
    donation: {
        amount: string;
        feeAmountRecovered: string | number;
        status: string;
        date: string;
        paymentMethod: string;
        mode: string;
    };
    details?: Array<{ label: string; [key: string]: any }>;
    isResolving: boolean;
}

/**
 * @unreleased
 */
export default function DonationStats({ donation, details, isResolving }: DonationStatsProps) {
    const { eventTicketsEnabled, adminUrl, isFeeRecoveryEnabled, currency } = getDonationOptionsWindowData();

    const amount = donation.amount;
    const feeAmountRecovered = donation.feeAmountRecovered;
    const eventTicketDetails = details?.filter(
        (detail: any) => detail.label === "Event Tickets"
    ) || [];

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
                    value={parseFloat((eventTicketDetails[0]?.value || '').replace(/[^0-9.]/g, '')) || 0}
                    formatter={amountFormatter(currency)}
                    loading={isResolving}
                />
            )}
            <StatWidget
                label={__('Fees recovered', 'give')}
                value={parseFloat(String(feeAmountRecovered))}
                formatter={amountFormatter(currency)}
                loading={isResolving}
                href={'https://givewp.com/addons/fee-recovery/'}
                inActive={!isFeeRecoveryEnabled}
            />
        </div>
    );
} 
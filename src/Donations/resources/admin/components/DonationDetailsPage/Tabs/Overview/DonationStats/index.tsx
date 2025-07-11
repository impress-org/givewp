
import {__} from '@wordpress/i18n';
import StatWidget from '@givewp/src/Admin/components/StatWidget';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';
import styles from './styles.module.scss';
import {Donation} from '@givewp/donations/admin/components/types';
import { useNormalizeDonation } from '@givewp/donations/hooks/useNormalizeDonation';

/**
 * @unreleased
 */
interface DonationStatsProps {
    donationStats: {
        amount: string;
        intendedAmount: string;
        eventTicketAmount?: string | null;
        feeAmountRecovered: string | number;
        status: string;
        date: string;
        paymentMethod: string;
        mode: string;
    };
    isResolving: boolean;
    donation: Donation;
}

/**
 * @unreleased
 */
export default function DonationStats({ donationStats, donation, isResolving }: DonationStatsProps) {
    const {isFeeRecoveryEnabled} = getDonationOptionsWindowData();
    const {eventTicketAmount} = donationStats;
    const shouldShowEventTicketStat = Number(eventTicketAmount) > 0;
    const {formatter, intendedAmount, feeAmountRecovered} = useNormalizeDonation(donation);

    return (
        <div className={styles.container}>
            <StatWidget
                label={__('Donation amount', 'give')}
                value={intendedAmount}
                formatter={formatter}
                loading={isResolving}
            />
            {shouldShowEventTicketStat && (
                <StatWidget
                    label={__('Event ticket', 'give')}
                    value={Number(eventTicketAmount)}
                    formatter={formatter}
                    loading={isResolving}
                />
            )}
            <StatWidget
                label={__('Fees recovered', 'give')}
                value={feeAmountRecovered}
                formatter={formatter}
                loading={isResolving}
                href={'https://givewp.com/addons/fee-recovery/'}
                inActive={!isFeeRecoveryEnabled}
            />
        </div>
    );
}

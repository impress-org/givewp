
import {__} from '@wordpress/i18n';
import StatWidget from '@givewp/src/Admin/components/StatWidget';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';
import styles from './styles.module.scss';
import {Donation} from '@givewp/donations/admin/components/types';
import { useDonationAmounts } from '@givewp/donations/hooks';

/**
 * @since 4.6.0
 */
interface DonationStatsProps {
    isResolving: boolean;
    donation: Donation;
}

/**
 * @since 4.6.0
 */
export default function DonationStats({ donation, isResolving }: DonationStatsProps) {
    const {isFeeRecoveryEnabled, eventTicketsEnabled} = getDonationOptionsWindowData();
    const shouldShowEventTicketStat = eventTicketsEnabled && Number(donation?.eventTicketsAmount?.value ?? 0) > 0;
    const {formatter, intendedAmount, feeAmountRecovered, eventTicketsAmount} = useDonationAmounts(donation);

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
                    value={Number(eventTicketsAmount)}
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

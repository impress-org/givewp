
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
 * @since 4.10.0 use upgrade object instead of inActive.
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
                value={formatter.format(intendedAmount)}
                loading={isResolving}
            />
            {shouldShowEventTicketStat && (
                <StatWidget
                    label={__('Event ticket', 'give')}
                    value={formatter.format(Number(eventTicketsAmount))}
                    loading={isResolving}
                />
            )}
            <StatWidget
                label={__('Fees recovered', 'give')}
                value={formatter.format(feeAmountRecovered)}
                loading={isResolving}
                upgrade={{
                    href: 'https://docs.givewp.com/fee-recovery-stats',
                    tooltip: __('Keep 100% of your fundraising revenue by providing donors with the option to cover the credit card processing fees.', 'give')
                }}
            />
        </div>
    );
}

import {__} from '@wordpress/i18n';
import StatWidget from '@givewp/src/Admin/components/StatWidget';
import {amountFormatter} from '@givewp/src/Admin/utils';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';
import styles from './styles.module.scss';
import { DonationStatistics } from '@givewp/donations/hooks/useDonationStatistics';

/**
 * @unreleased
 */
interface DonationStatsProps {
    stats: DonationStatistics['donation'];
    isResolving: boolean;
}

/**
 * @unreleased
 */
export default function DonationStats({ stats, isResolving }: DonationStatsProps) {
    const { isFeeRecoveryEnabled, currency: defaultCurrency } = getDonationOptionsWindowData();
    const {baseIntendedAmount, feeAmountRecovered, eventTicketAmount} = stats;
    const eventTicketValue = parseFloat(eventTicketAmount);
    const shouldShowEventTicketStat = eventTicketValue > 0;    

    return (
        <div className={styles.container}>
            <StatWidget
                label={__('Donation amount', 'give')}
                value={parseFloat(baseIntendedAmount) || 0}
                formatter={amountFormatter(defaultCurrency)}
                loading={isResolving}
            />
            {shouldShowEventTicketStat && (
                <StatWidget
                    label={__('Event ticket', 'give')}
                    value={eventTicketValue}
                    formatter={amountFormatter(defaultCurrency)}
                    loading={isResolving}
                />
            )}
            <StatWidget
                label={__('Fees recovered', 'give')}
                value={parseFloat(String(feeAmountRecovered))}
                formatter={amountFormatter(defaultCurrency)}
                loading={isResolving}
                href={'https://givewp.com/addons/fee-recovery/'}
                inActive={!isFeeRecoveryEnabled}
            />
        </div>
    );
}

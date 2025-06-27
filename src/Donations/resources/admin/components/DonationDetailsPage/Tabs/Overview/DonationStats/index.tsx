
import {__} from '@wordpress/i18n';
import StatWidget from '@givewp/src/Admin/components/StatWidget';
import {amountFormatter} from '@givewp/src/Admin/utils';
import {getDonationOptionsWindowData} from '@givewp/donations/utils';
import styles from './styles.module.scss';

/**
 * @unreleased
 */
interface DonationStatsProps {
    donation: {
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
    currency: string;
}

/**
 * @unreleased
 */
export default function DonationStats({ donation, isResolving, currency }: DonationStatsProps) {
    const { isFeeRecoveryEnabled, currency: defaultCurrency } = getDonationOptionsWindowData();
    const {intendedAmount, feeAmountRecovered, eventTicketAmount} = donation;
    const eventTicketValue = parseFloat(eventTicketAmount);
    const shouldShowEventTicketStat = eventTicketValue > 0;    

    return (
        <div className={styles.container}>
            <StatWidget
                label={__('Donation amount', 'give')}
                value={parseFloat(intendedAmount) || 0}
                formatter={amountFormatter(currency ?? defaultCurrency)}
                loading={isResolving}
            />
            {shouldShowEventTicketStat && (
                <StatWidget
                    label={__('Event ticket', 'give')}
                    value={eventTicketValue}
                    formatter={amountFormatter(currency ?? defaultCurrency)}
                    loading={isResolving}
                />
            )}
            <StatWidget
                label={__('Fees recovered', 'give')}
                value={parseFloat(String(feeAmountRecovered))}
                formatter={amountFormatter(currency ?? defaultCurrency)}
                loading={isResolving}
                href={'https://givewp.com/addons/fee-recovery/'}
                inActive={!isFeeRecoveryEnabled}
            />
        </div>
    );
}

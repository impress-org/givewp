import {OneTimeAmountMessage, RecurringAmountMessage} from '@givewp/forms/shared/AmountMessages';
import {isSubscriptionPeriod} from './subscriptionPeriod';
import {isDonationTypeSubscription} from '@givewp/forms/types';

/**
 * @since 3.0.0
 */
export default function DonationAmountMessage({
    isFixedAmount,
    subscriptionDetailsAreFixed,
}) {
    const {useWatch, useCurrencyFormatter} = window.givewp.form.hooks;

    const amount = useWatch({name: 'amount'});
    const currency = useWatch({name: 'currency'});
    const formatter = useCurrencyFormatter(currency);
    const fixedAmountFormatted = formatter.format(Number(amount));

    const donationType = useWatch({name: 'donationType'});
    const subscriptionPeriod = useWatch({name: 'subscriptionPeriod'});
    const subscriptionFrequency = useWatch({name: 'subscriptionFrequency'});
    const subscriptionInstallments = useWatch({name: 'subscriptionInstallments'});
    const isSubscription = isDonationTypeSubscription(donationType) && isSubscriptionPeriod(subscriptionPeriod);

    const subscriptionHasMoreDetails = subscriptionFrequency > 1 || subscriptionInstallments > 0;
    const displayFixedAmountSubscriptionMessage =
        isSubscription && (subscriptionHasMoreDetails || subscriptionDetailsAreFixed);

    return (
        <>
            {isFixedAmount && !displayFixedAmountSubscriptionMessage && (
                <div className="givewp-fields-amount__fixed-message">
                    <OneTimeAmountMessage amount={fixedAmountFormatted} />
                </div>
            )}

            {displayFixedAmountSubscriptionMessage && (
                <div className="givewp-fields-amount__fixed-message">
                    <RecurringAmountMessage
                        isFixedAmount={isFixedAmount}
                        fixedAmount={fixedAmountFormatted}
                        period={subscriptionPeriod}
                        frequency={subscriptionFrequency}
                        installments={subscriptionInstallments}
                    />
                </div>
            )}
        </>
    );
}

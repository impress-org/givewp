import FixedAmountMessage from './FixedAmountMessage';
import FixedAmountSubscriptionMessage from './FixedAmountRecurringMessage';

/**
 * @unreleased
 */
export default function DonationAmountMessage({
    isFixedAmount,
    fixedAmountValue,
    subscriptionDetailsAreFixed,
}) {
    const {useWatch, useCurrencyFormatter} = window.givewp.form.hooks;

    const currency = useWatch({name: 'currency'});
    const formatter = useCurrencyFormatter(currency);
    const fixedAmountFormatted = formatter.format(Number(fixedAmountValue));

    const donationType = useWatch({name: 'donationType'});
    const subscriptionPeriod = useWatch({name: 'subscriptionPeriod'});
    const subscriptionFrequency = useWatch({name: 'subscriptionFrequency'});
    const subscriptionInstallments = useWatch({name: 'subscriptionInstallments'});

    const subscriptionHasMoreDetails = subscriptionFrequency > 1 || subscriptionInstallments > 0;
    const displayFixedAmountSubscriptionMessage =
        donationType === 'subscription' && (subscriptionHasMoreDetails || subscriptionDetailsAreFixed);

    return (
        <>
            {isFixedAmount && !displayFixedAmountSubscriptionMessage && (
                <FixedAmountMessage amount={fixedAmountFormatted} />
            )}

            {displayFixedAmountSubscriptionMessage && (
                <FixedAmountSubscriptionMessage
                    isFixedAmount={isFixedAmount}
                    fixedAmount={fixedAmountFormatted}
                    period={subscriptionPeriod}
                    frequency={subscriptionFrequency}
                    installments={subscriptionInstallments}
                />
            )}
        </>
    );
}

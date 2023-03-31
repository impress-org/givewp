import {DonationAmountProps} from '@givewp/forms/propTypes';
import DonationAmountMessage from './DonationAmountMessage';
import useDonationType from './useDonationType';

/**
 * @unreleased
 */
export default function DonationAmount({
    fields: {
        amount: AmountField,
        donationType: DonationTypeField,
        currency: CurrencyField,
        subscriptionPeriod: SubscriptionPeriodField,
        subscriptionInstallments: SubscriptionInstallmentsField,
        subscriptionFrequency: SubscriptionFrequencyField,
    },
    fieldProps: {amount: amountProps},
    subscriptionsEnabled,
    subscriptionDetailsAreFixed,
}: DonationAmountProps) {
    useDonationType();
    const {allowLevels, fixedAmountValue, allowCustomAmount} = amountProps;

    return (
        <>
            {subscriptionsEnabled && <SubscriptionPeriodField />}
            <CurrencyField />
            <DonationTypeField />
            <AmountField />
            <DonationAmountMessage
                isFixedAmount={!allowCustomAmount && !allowLevels}
                fixedAmountValue={fixedAmountValue}
                subscriptionDetailsAreFixed={subscriptionDetailsAreFixed}
            />
            {subscriptionsEnabled && <SubscriptionFrequencyField />}
            {subscriptionsEnabled && <SubscriptionInstallmentsField />}
        </>
    );
}

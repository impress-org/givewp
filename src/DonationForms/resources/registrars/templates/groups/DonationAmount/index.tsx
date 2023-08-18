import {DonationAmountProps} from '@givewp/forms/propTypes';
import DonationAmountMessage from './DonationAmountMessage';
import useDonationType from './useDonationType';
import DonationAmountCurrencySwitcherMessage from './DonationAmountCurrencySwitcherMessage';

/**
 * @since 3.0.0
 */
export default function DonationAmount({
    nodeComponents: {
        amount: AmountField,
        donationType: DonationTypeField,
        currency: CurrencyField,
        subscriptionPeriod: SubscriptionPeriodField,
        subscriptionInstallments: SubscriptionInstallmentsField,
        subscriptionFrequency: SubscriptionFrequencyField,
    },
    nodeProps: {amount: amountProps},
    subscriptionsEnabled,
    subscriptionDetailsAreFixed,
}: DonationAmountProps) {
    useDonationType();
    const {useDonationFormSettings} = window.givewp.form.hooks;
    const {allowLevels, allowCustomAmount} = amountProps;
    const {currencySwitcherSettings, currencySwitcherMessage} = useDonationFormSettings();

    return (
        <>
            {subscriptionsEnabled && <SubscriptionPeriodField />}
            <CurrencyField />
            <DonationTypeField />
            <AmountField
                messages={
                    <>
                        <DonationAmountMessage
                            isFixedAmount={!allowCustomAmount && !allowLevels}
                            subscriptionDetailsAreFixed={subscriptionDetailsAreFixed}
                        />

                        {currencySwitcherSettings.length > 1 && currencySwitcherMessage && (
                            <DonationAmountCurrencySwitcherMessage
                                currencySettings={currencySwitcherSettings}
                                message={currencySwitcherMessage}
                            />
                        )}
                    </>
                }
            />
            {subscriptionsEnabled && <SubscriptionFrequencyField />}
            {subscriptionsEnabled && <SubscriptionInstallmentsField />}
        </>
    );
}

import {CurrencySwitcherSetting} from '@givewp/forms/types';
import CurrencySwitcher, {calculateCurrencyAmount} from './CurrencySwitcher';

/**
 * @since 3.0.0
 */
export default function DonationAmountCurrency({currencySettings, onCurrencyAmountChange}: {
    currencySettings: CurrencySwitcherSetting[],
    onCurrencyAmountChange?: (amount: number) => void
}) {
    const {useWatch, useCurrencyFormatter, useFormContext} = window.givewp.form.hooks;
    const {setValue, getValues} = useFormContext();

    const currency = useWatch({name: 'currency'});
    const formatter = useCurrencyFormatter(currency);
    const currencySymbol = formatter.formatToParts().find(({type}) => type === 'currency').value;

    return currencySettings.length > 1 ? (
        <CurrencySwitcher
            defaultCurrency={currency}
            currencySettings={currencySettings}
            onSelect={(event) => {
                const selectedCurrency = event.target.value;

                const currencyAmount = calculateCurrencyAmount(
                    getValues('amount'),
                    currency,
                    selectedCurrency,
                    currencySettings
                );

                setValue('currency', selectedCurrency);
                setValue('amount', currencyAmount);

                onCurrencyAmountChange(currencyAmount);
            }}
        />
    ) : (
        <span className="givewp-fields-amount__currency-container">
            <span>{currency}</span>
            <span>{currencySymbol}</span>
        </span>
    );
}

import {CurrencySwitcherSetting} from '@givewp/forms/types';
import {__} from '@wordpress/i18n';

/**
 * @unreleased
 */
type CurrencySwitcherMessageProps = {
    message: string;
    baseCurrency: string;
    newCurrencyRate: string;
    newCurrency: string;
};

/**
 * @unreleased
 */
type DonationAmountCurrencySwitcherMessageProps = {
    currencySettings: CurrencySwitcherSetting[];
    message: string;
};

/**
 * @unreleased
 */
const CurrencySwitcherMessage = ({
                                     message,
                                     baseCurrency,
                                     newCurrencyRate,
                                     newCurrency,
                                 }: CurrencySwitcherMessageProps) => {
    if (baseCurrency === newCurrency) {
        return;
    }

    const templateTags = {
        base_currency: baseCurrency,
        new_currency_rate: newCurrencyRate,
        new_currency: newCurrency,
    };

    Object.keys(templateTags).forEach((key) => {
        message = message.replace(`{${key}}`, templateTags[key]);
    });

    return <div className="givewp-fields-amount__currency-switcher-message">{__(message, 'give')}</div>;
};

/**
 * @unreleased
 */
export default function DonationAmountCurrencySwitcherMessage({
    currencySettings,
    message,
}: DonationAmountCurrencySwitcherMessageProps) {
    const {useWatch, useCurrencyFormatter} = window.givewp.form.hooks;
    const currency = useWatch({name: 'currency'});

    const baseCurrency = currencySettings.find((setting) => setting.exchangeRate === 0)?.id ?? 'USD';

    const baseCurrencyFormatter = useCurrencyFormatter(baseCurrency);

    const newCurrencySetting = currencySettings.find((setting) => setting.id === currency);

    const newCurrencyRate = newCurrencySetting?.exchangeRate ?? Number('1.00');

    const newCurrencyRateFormatter = useCurrencyFormatter(currency, {
        minimumFractionDigits: newCurrencySetting.exchangeRateFractionDigits,
    });

    return (
        <CurrencySwitcherMessage
            baseCurrency={baseCurrency}
            message={message.replace('1.00', baseCurrencyFormatter.format(1))}
            newCurrency={currency}
            newCurrencyRate={newCurrencyRateFormatter.format(newCurrencyRate)}
        />
    );
}

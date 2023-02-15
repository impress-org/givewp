import CurrencyInput, {CurrencyInputProps, formatValue} from 'react-currency-input-field';
import {BaseControl} from '@wordpress/components';

const {currency = 'USD'} = window?.storageData ?? {};

const formatCurrencyAmount = (amount: string) => {
    return formatValue({
        value: amount,
        intlConfig: {locale: window.navigator.language, currency},
    });
};

const Currency = ({amount}: {amount: string}) => {
    return formatCurrencyAmount(amount);
};

interface CurrencyControlProps extends CurrencyInputProps {
    label: string;
    hideLabelFromVision?: boolean;
}

const CurrencyControl = ({label, hideLabelFromVision, ...rest}: CurrencyControlProps) => {
    return (
        <BaseControl label={label} id={label.toLowerCase().replace(' ', '-')} hideLabelFromVision={hideLabelFromVision}>
            <CurrencyInput
                {...rest}
                className={'components-text-control__input'}
                allowDecimals={true}
                allowNegativeValue={false}
                maxLength={9}
                intlConfig={{locale: window.navigator.language, currency}}
            />
        </BaseControl>
    );
};

export {
    currency,
    formatCurrencyAmount,
    Currency,
    CurrencyControl,
};

import CurrencyInput, {formatValue} from 'react-currency-input-field';
import {BaseControl} from "@wordpress/components";

const {currency = 'USD'} = window?.storageData?.currency ?? {};

const formatCurrencyAmount = (amount) => {
    return formatValue({
        value: amount,
        intlConfig: {locale: window.navigator.language, currency},
    });
}

const Currency = ({amount}) => {
    return formatCurrencyAmount(amount);
};

const CurrencyControl = (props) => {
    return (
        <BaseControl label={ props.label }>
            <CurrencyInput
                {...props}
                className={"components-text-control__input"}
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

import CurrencyInput, {CurrencyInputProps, formatValue} from 'react-currency-input-field';
import {BaseControl} from '@wordpress/components';
import {useInstanceId} from '@wordpress/compose';

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
    label?: string;
    hideLabelFromVision?: boolean;
}

const CurrencyControl = ({label, hideLabelFromVision, ...rest}: CurrencyControlProps) => {
    // simplified implementation of useBaseControlProps()
    const uniqueId = useInstanceId(BaseControl, 'wp-components-base-control');

    return (
        <BaseControl label={label} id={uniqueId} hideLabelFromVision={hideLabelFromVision}>
            <CurrencyInput
                {...rest}
                id={uniqueId}
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

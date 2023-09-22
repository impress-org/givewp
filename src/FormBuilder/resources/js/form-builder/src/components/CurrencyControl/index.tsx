import CurrencyInput, {CurrencyInputProps, formatValue} from 'react-currency-input-field';
import {BaseControl} from '@wordpress/components';
import {useInstanceId} from '@wordpress/compose';
import {useState} from '@wordpress/element';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const formatCurrencyAmount = (amount: string) => {
    const {currency = 'USD'} = getFormBuilderWindowData();

    return formatValue({
        value: amount,
        intlConfig: {locale: window.navigator.language, currency},
    });
};

function parseValueFromLocale(amount: string): string {
    if (!amount) {
        return amount;
    }

    const numberFormat = new Intl.NumberFormat(window.navigator.language);
    const parts = numberFormat.formatToParts(1234.56);

    let groupSeparator: string;
    let decimalSeparator: string;

    for (const part of parts) {
        if (part.type === 'group') {
            groupSeparator = part.value;
        } else if (part.type === 'decimal') {
            decimalSeparator = part.value;
        }
    }

    return amount.replaceAll(groupSeparator, '').replace(decimalSeparator, '.');
}

const Currency = ({amount}: {amount: string}) => {
    return formatCurrencyAmount(amount);
};

interface CurrencyControlProps extends CurrencyInputProps {
    label?: string;
    hideLabelFromVision?: boolean;
    help?: string;
}

const CurrencyControl = ({label, help, hideLabelFromVision, value, onValueChange, ...rest}: CurrencyControlProps) => {
    const [localizedValue, setLocalizedValue] = useState(value);

    const {currency = 'USD'} = getFormBuilderWindowData();
    // simplified implementation of useBaseControlProps()
    const uniqueId = useInstanceId(BaseControl, 'wp-components-base-control');

    const updateValue = (value: string) => {
        setLocalizedValue(value);
        onValueChange(parseValueFromLocale(value));
    };

    return (
        <BaseControl label={label} help={help} id={uniqueId} hideLabelFromVision={hideLabelFromVision}>
            <CurrencyInput
                id={uniqueId}
                value={localizedValue}
                onValueChange={updateValue}
                className={'components-text-control__input'}
                allowDecimals={true}
                allowNegativeValue={false}
                maxLength={9}
                intlConfig={{locale: window.navigator.language, currency}}
                {...rest}
            />
        </BaseControl>
    );
};

export {formatCurrencyAmount, Currency, CurrencyControl};

import CurrencyInput, {CurrencyInputProps, formatValue} from 'react-currency-input-field';
import {BaseControl} from '@wordpress/components';
import {useInstanceId} from '@wordpress/compose';
import {getFormBuilderWindowData} from '@givewp/form-builder/common/getWindowData';

const formatCurrencyAmount = (amount: string) => {
    const {currency = 'USD'} = getFormBuilderWindowData();

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
    help?: string;
}

const CurrencyControl = ({label, help, hideLabelFromVision, ...rest}: CurrencyControlProps) => {
    const {currency = 'USD'} = getFormBuilderWindowData();
    // simplified implementation of useBaseControlProps()
    const uniqueId = useInstanceId(BaseControl, 'wp-components-base-control');

    return (
        <BaseControl label={label} help={help} id={uniqueId} hideLabelFromVision={hideLabelFromVision}>
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
    formatCurrencyAmount,
    Currency,
    CurrencyControl,
};

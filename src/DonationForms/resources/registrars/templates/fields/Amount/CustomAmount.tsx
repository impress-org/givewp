import classNames from 'classnames';
import {__} from '@wordpress/i18n';
import CurrencyInput from 'react-currency-input-field';
import { CurrencyInputOnChangeValues } from "react-currency-input-field/dist/components/CurrencyInputProps";

/**
 * @since 3.0.0
 */
type CustomAmountProps = {
    fieldError?: string;
    currency?: string;
    currencySymbol?: string;
    defaultValue?: string;
    value?: string;
    onValueChange?: (value: string, name?: string, values?: CurrencyInputOnChangeValues) => void;
};

/**
 * @since 3.0.0
 */
const CustomAmount = (
    {defaultValue, fieldError, currency, value, onValueChange}: CustomAmountProps
) => {
    return (
        <div className={classNames('givewp-fields-amount__input-container', {invalid: fieldError})}>
            <CurrencyInput
                intlConfig={{
                    locale: navigator.language,
                    currency,
                }}
                className="givewp-fields-amount__input givewp-fields-amount__input-custom"
                aria-invalid={fieldError ? 'true' : 'false'}
                id="amount-custom"
                name="amount-custom"
                placeholder={__('Enter custom amount', 'give')}
                defaultValue={defaultValue}
                value={value}
                decimalsLimit={2}
                onValueChange={(value) => onValueChange(value !== undefined ? value : '')}
            />
        </div>
    );
};

export default CustomAmount;

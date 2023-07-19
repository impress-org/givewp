import classNames from 'classnames';
import {__} from '@wordpress/i18n';
import CurrencyInput from 'react-currency-input-field';

/**
 * @unreleased add value prop
 * @since 0.2.0
 */
type CustomAmountProps = {
    fieldError?: string;
    currency?: string;
    currencySymbol?: string;
    defaultValue?: number;
    value?: string;
    onValueChange?: (value: string) => void;
};

/**
 * @unreleased remove forwardRef and use state for value instead
 * @since 0.2.0
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
                placeholder={__('Custom amount', 'give')}
                defaultValue={defaultValue}
                value={value}
                decimalsLimit={2}
                onValueChange={onValueChange}
            />
        </div>
    );
};

export default CustomAmount;
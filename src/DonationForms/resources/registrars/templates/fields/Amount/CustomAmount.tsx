import classNames from 'classnames';
import {__} from '@wordpress/i18n';
import CurrencyInput from 'react-currency-input-field';
import {CurrencyInputOnChangeValues} from 'react-currency-input-field/dist/components/CurrencyInputProps';

import styles from "../../styles.module.scss"
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

const formatter = new Intl.NumberFormat(navigator.language);
const groupSeparator = formatter.format(1000).replace(/[0-9]/g, '');
const decimalSeparator = formatter.format(1.1).replace(/[0-9]/g, '');

/**
 * @unreleased add module styles to override currency-input placeholder styling.
 * @since 3.0.0
 */
const CustomAmount = ({defaultValue, fieldError, currency, value, onValueChange}: CustomAmountProps) => {
    return (
        <div className={classNames('givewp-fields-amount__input-container', {invalid: fieldError})}>
            <CurrencyInput
                ref={(input) => {
                    if (input && fieldError) {
                        input.focus();
                    }
                }}
                intlConfig={{
                    locale: navigator.language,
                    currency,
                }}
                disableAbbreviations
                decimalSeparator={decimalSeparator}
                groupSeparator={
                    /**
                     * Replace non-breaking space to avoid conflict with the suffix separator.
                     * @link https://github.com/cchanxzy/react-currency-input-field/issues/266
                     */
                    groupSeparator.replace(/\u00A0/g, ' ')
                }
                className={`${styles.customAmount} givewp-fields-amount__input givewp-fields-amount__input-custom`}
                aria-label={__('Enter custom amount', 'give')}
                aria-describedby={fieldError ? 'givewp-field-error-amount' : undefined}
                aria-invalid={fieldError ? 'true' : 'false'}
                id="amount-custom"
                placeholder={__('Enter custom amount', 'give')}
                defaultValue={defaultValue}
                value={value}
                decimalsLimit={2}
                onValueChange={(value) => {
                    onValueChange(value !== undefined ? value : '');
                }}
            />
        </div>
    );
};

export default CustomAmount;

import classNames from 'classnames';
import {__} from "@wordpress/i18n";
import CurrencyInput from "react-currency-input-field";
import {forwardRef} from "@wordpress/element";
import {ForwardedRef, RefObject} from "react";

/**
 * @unreleased
 */
type CustomAmountProps = {
    fieldError?: string;
    currency?: string;
    currencySymbol?: string;
    defaultValue?: number;
    onValueChange?: (value: string) => void;
}

/**
 * @unreleased
 */
const CustomAmount = forwardRef(({
                                     defaultValue,
                                     fieldError,
                                     currency,
                                     currencySymbol,
                                     onValueChange,
                                 }: CustomAmountProps, ref: ForwardedRef<HTMLInputElement>) => {
    return (
        <div className={classNames('givewp-fields-amount__input--container', {invalid: fieldError})}>
            {currencySymbol && !currency && (
                <span className="givewp-fields-amount__input--currency-symbol">
                    {currencySymbol}
                </span>
            )}
            <CurrencyInput
                ref={ref}
                intlConfig={{
                    locale: navigator.language, currency,
                }}
                className="givewp-fields-amount__input givewp-fields-amount__input--custom"
                aria-invalid={fieldError ? 'true' : 'false'}
                id="amount-custom"
                name="amount-custom"
                placeholder={__('Custom amount', 'give')}
                defaultValue={defaultValue}
                decimalsLimit={2}
                onValueChange={onValueChange}
                customInput={forwardRef((props, ref: RefObject<HTMLInputElement>) => {
                    // This is necessary to make sure the internal value of the controlled input gets cleared when the ref is updated.
                    // Otherwise, the input will remember the previous value before the ref value was updated which is not ideal.
                    if (ref?.current?.value === '') {
                        props = {...props, value: ''};
                    }

                    return <input {...props} ref={ref}/>
                })}
            />
        </div>
    );
});

export default CustomAmount;
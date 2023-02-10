import {useRef} from '@wordpress/element';
import type {AmountProps} from '@givewp/forms/propTypes';
import FixedAmountMessage from './FixedAmountMessage';
import CustomAmount from './CustomAmount';
import AmountLevels from './AmountLevels';

/**
 * @unreleased add display options for multi levels, fixed amount, and custom amount
 * @since 0.1.0
 */
export default function Amount({
                                   name,
                                   defaultValue,
                                   Label,
                                   ErrorMessage,
                                   inputProps,
                                   fieldError,
                                   allowLevels,
                                   levels,
                                   fixedAmountValue,
                                   allowCustomAmount,
                               }: AmountProps) {
    const customAmountInputRef = useRef<HTMLInputElement>(null);
    const {useWatch, useFormContext, useCurrencyFormatter} = window.givewp.form.hooks;
    const {setValue} = useFormContext();
    const currency = useWatch({name: 'currency'});
    const formatter = useCurrencyFormatter(currency);
    const currencySymbol = formatter.formatToParts().find(({type}) => type === 'currency').value;

    const isFixedAmount = !allowLevels;
    const displayFixedAmountMessage = !allowCustomAmount && isFixedAmount;
    const resetCustomAmountInput = () => {
        customAmountInputRef.current.value = '';
        customAmountInputRef.current.attributes.getNamedItem("value").value = '';
    }

    return (
        <>
            <div className="givewp-fields-amount__directions">
                <label
                    className="givewp-fields-amount__input--label"
                    htmlFor={name}
                    aria-labelledby={name}
                >
                    <Label/>
                </label>

                {/* TODO: Control currency input from here*/}
                <span className="givewp-fields-amount__currency--container">
                    <span>{currency}</span>
                    <span>{currencySymbol}</span>
                </span>
            </div>

            {allowLevels && (
                <AmountLevels
                    name={name}
                    currency={currency}
                    levels={levels}
                    onLevelClick={(levelAmount) => {
                        resetCustomAmountInput();
                        setValue(name, levelAmount);
                    }}
                />
            )}

            {allowCustomAmount && (
                <CustomAmount
                    ref={customAmountInputRef}
                    fieldError={fieldError}
                    defaultValue={isFixedAmount ? fixedAmountValue : null}
                    currency={currency}
                    currencySymbol={currencySymbol}
                    onValueChange={(value) => {
                        setValue(name, value ?? null)
                    }}
                />
            )}

            {displayFixedAmountMessage && (
                <FixedAmountMessage
                    amount={formatter.format(Number(fixedAmountValue))}
                />
            )}

            <input
                type="hidden"
                {...inputProps}
            />

            <ErrorMessage/>
        </>
    );
}

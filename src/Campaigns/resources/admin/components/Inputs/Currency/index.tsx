import CurrencyInput from 'react-currency-input-field';
import {Controller, useFormContext} from 'react-hook-form';

type Props = {
    name: string;
    currency: string;
    placeholder?: string;
    disabled?: boolean;
};

/**
 * @since 4.0.0
 */
function getNumberFormattingParts(): { groupSeparator: string; decimalSeparator: string } {
    const numberFormat = new Intl.NumberFormat(window.navigator.language);
    const parts = numberFormat.formatToParts(1234.56);

    let groupSeparator = '';
    let decimalSeparator = '';

    for (const part of parts) {
        if (part.type === 'group') {
            groupSeparator = part.value;
        } else if (part.type === 'decimal') {
            decimalSeparator = part.value;
        }
    }

    return {groupSeparator, decimalSeparator};
}

/**
 * @since 4.0.0
 */
export default ({name, currency, placeholder, disabled, ...rest}: Props) => {
    const {control} = useFormContext();
    const {groupSeparator, decimalSeparator} = getNumberFormattingParts();

    return (
        <Controller
            name={name}
            control={control}
            render={({field}) => (
                <CurrencyInput
                    disabled={disabled}
                    disableAbbreviations
                    decimalSeparator={decimalSeparator}
                    groupSeparator={
                        /**
                         * Replace non-breaking space to avoid conflict with the suffix separator.
                         * @link https://github.com/cchanxzy/react-currency-input-field/issues/266
                         */
                        groupSeparator.replace(/\u00A0/g, ' ')
                    }
                    onValueChange={(value) => {
                        field.onChange(Number(value ?? 0));
                    }}
                    value={field.value}
                    placeholder={placeholder}
                    allowDecimals={true}
                    allowNegativeValue={false}
                    maxLength={9}
                    intlConfig={{locale: window.navigator.language, currency}}
                    {...rest}
                />
            )}
        />
    );
};

import CurrencyInput from 'react-currency-input-field';
import {Controller, useFormContext} from 'react-hook-form';

type Props = {
    name: string;
    currency: string;
    placeholder?: string;
    disabled?: boolean;
};

/**
 * @unreleased
 */
export default ({name, currency, placeholder, disabled, ...rest}: Props) => {
    const {control} = useFormContext();

    return (
        <Controller
            name={name}
            control={control}
            render={({field}) => (
                <CurrencyInput
                    disabled={disabled}
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

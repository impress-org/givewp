import CurrencyInput from 'react-currency-input-field';
import {Controller, useFormContext} from 'react-hook-form';

type Props = {
    name: string;
    currency: string;
};

export default ({
    name,
    currency,
    ...rest
}: Props) => {
    const {control} = useFormContext();

    return (
        <Controller
            name={name}
            control={control}
            render={({field}) => (
                <CurrencyInput
                    onValueChange={(value) => {
                        field.onChange(Number(value ?? 0));
                    }}
                    value={field.value}
                    allowDecimals={true}
                    allowNegativeValue={false}
                    maxLength={9}
                    intlConfig={{locale: window.navigator.language, currency}}
                    {...rest}
                />
            )}
        />
    );
}

import type {FieldProps} from '@givewp/forms/propTypes';
import {useMemo} from 'react';
import {useFormContext, useWatch} from 'react-hook-form';

interface AmountProps extends FieldProps {
    levels: bigint[];
    allowCustomAmount: boolean;
}

export default function Amount({name, label, inputProps, levels, allowCustomAmount, fieldError}: AmountProps) {
    const {setValue} = useFormContext();
    const currency = useWatch({name: 'currency'});
    const formatter: Intl.NumberFormat = useMemo(
        () =>
            new Intl.NumberFormat(navigator.language, {
                style: 'currency',
                currency: currency,
            }),
        [currency, navigator.language]
    );

    return (
        <div>
            {levels.map((levelAmount) => {
                const label = formatter.format(levelAmount);
                return (
                    <button type="button" onClick={() => setValue(name, levelAmount)} key={label}>
                        {label}
                    </button>
                );
            })}
            <label>
                {label}
                <input type={allowCustomAmount ? 'text' : 'hidden'} {...inputProps} />
            </label>
            {fieldError && <p>{fieldError}</p>}
        </div>
    );
}

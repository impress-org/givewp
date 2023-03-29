import {useFormContext} from 'react-hook-form';

/**
 *
 * @unreleased
 */
export default function HiddenFields() {
    const {register} = useFormContext();
    return (
        <>
            <input hidden {...register('amount')} />
            <input hidden {...register('feeAmountRecovered')} />
            <input hidden {...register('createdAt')} />
        </>
    );
}

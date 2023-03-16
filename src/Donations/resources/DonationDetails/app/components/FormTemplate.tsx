import {useFormContext} from 'react-hook-form';

/**
 *
 * @unreleased
 */

export default function FormTemplate({}) {
    const methods = useFormContext();
    const {register} = methods;

    const {errors} = methods.formState;

    return (
        <>
            <input {...register('test')} />
        </>
    );
}

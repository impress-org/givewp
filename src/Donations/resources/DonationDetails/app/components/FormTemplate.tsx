import {useFormContext} from 'react-hook-form';
import PaymentInformation from './PaymentInformation';
import {FormTemplate} from './types';

/**
 *
 * @unreleased
 */

export default function FormTemplate({data}: FormTemplate) {
    const methods = useFormContext();
    const {register, setValue, getValues} = methods;

    const {errors} = methods.formState;

    return (
        <>
            <PaymentInformation data={data} register={register} setValue={setValue} />
        </>
    );
}

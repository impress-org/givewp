import {useFormContext} from 'react-hook-form';
import {FormTemplate} from './types';
import PaymentInformation from './PaymentInformation';

/**
 *
 * @unreleased
 */

export default function FormTemplate({}: FormTemplate) {
    const methods = useFormContext();
    const {register} = methods;

    const {errors} = methods.formState;

    return (
        <>
            <PaymentInformation />
        </>
    );
}

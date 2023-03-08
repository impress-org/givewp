import {useFormContext} from 'react-hook-form';
import {FormTemplate} from '../../types';

/**
 *
 * @unreleased
 */

export default function FormTemplate({}: FormTemplate) {
    const methods = useFormContext();
    const {register} = methods;

    const {errors} = methods.formState;

    return <></>;
}

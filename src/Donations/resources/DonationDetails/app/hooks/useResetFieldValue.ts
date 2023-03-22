import {useFormContext} from 'react-hook-form';
import {defaultFormValues} from '../utilities/defaultFormValues';

/**
 *
 * @unreleased
 */
export default function useResetFieldValue(fieldName: string) {
    const methods = useFormContext();
    const {reset} = methods;

    return (fieldName) => {
        reset({
            ...defaultFormValues,
            [fieldName]: defaultFormValues[fieldName],
        });
    };
}

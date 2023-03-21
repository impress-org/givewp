import {useFormContext} from 'react-hook-form';
import {defaultFormValues} from '../config';

export default function useResetFieldValue(fieldName: string) {
    const methods = useFormContext();
    const {reset} = methods;

    const resetFieldValue = (fieldName) => {
        reset({
            ...defaultFormValues,
            [fieldName]: defaultFormValues[fieldName],
        });
    };

    return resetFieldValue;
}

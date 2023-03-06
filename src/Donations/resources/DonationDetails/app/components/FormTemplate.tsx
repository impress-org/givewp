import {useFormContext} from 'react-hook-form';
import {FormTemplate} from "../../types";

export default function FormTemplate({defaultValues}: FormTemplate) {
    const methods = useFormContext();
    const {register} = methods;

    const {errors} = methods.formState;
    const {status} = defaultValues;

    return (
        <>

        </>
    );
}

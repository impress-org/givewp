import {FormProvider, useForm} from 'react-hook-form';
import {joiResolver} from '@hookform/resolvers/joi';
import {FormPage} from "@givewp/components/AdminUI/types";

export default function FormPage({id, handleSubmitRequest, defaultValues, validationSchema, children}: FormPage) {
    const methods = useForm({
        defaultValues: defaultValues,
        resolver: joiResolver(validationSchema),
    });

    const {handleSubmit} = methods;

    return (
        <FormProvider {...methods}>
            <header>
                <button form={id} type='submit' onSubmit={handleSubmit(handleSubmitRequest)}>
                    Submit
                </button>
            </header>
            <form id={id} onSubmit={handleSubmit(handleSubmitRequest)}>
                {children}
            </form>
        </FormProvider>
    );
}

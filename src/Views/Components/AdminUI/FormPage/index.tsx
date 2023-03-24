import React, {createContext, useRef} from 'react';
import {FormProvider, useForm} from 'react-hook-form';

import FormNavigation from '@givewp/components/AdminUI/FormNavigation';
import {Form} from '@givewp/components/AdminUI/FormElements';

import {FormPageProps} from './types';
import A11yDialogInstance from 'a11y-dialog';
import {usePostRequest} from '@givewp/components/AdminUI/api';
import {joiResolver} from '@hookform/resolvers/joi';

/**
 *
 * @unreleased
 */

export const ModalContext = createContext((label, content, confirmationAction, exitCallback, button, notice) => {});

export default function FormPage({
    formId,
    endpoint,
    defaultValues,
    pageInformation,
    validationSchema,
    children,
    actionConfig,
}: FormPageProps) {
    const {postData} = usePostRequest(endpoint);

    const dialog = useRef() as {current: A11yDialogInstance};

    const methods = useForm({
        defaultValues: defaultValues,
        resolver: joiResolver(validationSchema),
    });

    const {handleSubmit, getValues} = methods;

    const {isDirty} = methods.formState;

    const handleSubmitRequest = async (formFieldValues) => {
        try {
            alert(JSON.stringify(formFieldValues));
            console.log(JSON.stringify(formFieldValues));
            await postData(formFieldValues);
        } catch (error) {
            alert(error);
        }
    };

    return (
        <FormProvider {...methods}>
            <FormNavigation
                pageInformation={pageInformation}
                onSubmit={handleSubmit(handleSubmitRequest)}
                actionConfig={actionConfig}
                isDirty={isDirty}
            />
            <Form id={formId} onSubmit={handleSubmit(handleSubmitRequest)}>
                {children}
            </Form>
        </FormProvider>
    );
}

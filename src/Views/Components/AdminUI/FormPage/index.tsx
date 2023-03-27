import React, {createContext} from 'react';
import {FormProvider, useForm} from 'react-hook-form';

import FormNavigation from '@givewp/components/AdminUI/FormNavigation';
import {Form} from '@givewp/components/AdminUI/FormElements';

import {FormPageProps} from './types';
import {joiResolver} from '@hookform/resolvers/joi';
import {PostRequest} from '@givewp/components/AdminUI/api';

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
    apiNonce,
}: FormPageProps) {
    const {postData} = PostRequest(endpoint, apiNonce);

    const methods = useForm({
        defaultValues: defaultValues,
        resolver: joiResolver(validationSchema),
    });

    const {handleSubmit, getValues} = methods;

    const {isDirty} = methods.formState;

    const handleSubmitRequest = async (formFieldValues) => {
        try {
            console.log(JSON.stringify(formFieldValues));
            console.log(endpoint);
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

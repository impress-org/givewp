import React, {createContext, useState} from 'react';
import {FormProvider, useForm} from 'react-hook-form';

import FormNavigation from '@givewp/components/AdminUI/FormNavigation';
import Toast from '@givewp/components/AdminUI/Toast';
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
    successMessage,
    errorMessage,
}: FormPageProps) {
    const {postData, result} = PostRequest(endpoint, apiNonce, successMessage, errorMessage);
    const [showApiMessage, setApiShowMessage] = useState(false);

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
            setApiShowMessage(true);
        } catch (error) {
            alert(error);
            setApiShowMessage(true);
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
            <Toast
                showMessage={showApiMessage}
                closeMessage={() => setApiShowMessage(false)}
                resultMessage={result.message}
                resultType={result.type}
            />
            <Form id={formId} onSubmit={handleSubmit(handleSubmitRequest)}>
                {children}
            </Form>
        </FormProvider>
    );
}

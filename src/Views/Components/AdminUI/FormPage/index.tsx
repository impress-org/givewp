import React, {useState} from 'react';
import {FormProvider, useForm} from 'react-hook-form';

import FormNavigation from '@givewp/components/AdminUI/FormNavigation';
import Toast from '@givewp/components/AdminUI/Toast';
import {Form} from '@givewp/components/AdminUI/FormElements';

import {joiResolver} from '@hookform/resolvers/joi';
import {usePostRequest} from '@givewp/components/AdminUI/api';
import {format} from 'date-fns';

/**
 *
 * @unreleased
 */

export interface PageInformation {
    id: number;
    description: string;
    title: string;
}

export interface FormPageProps {
    formId: string;
    endpoint: string;
    apiNonce: string;
    errorMessage: string;
    successMessage: string;
    defaultValues;
    validationSchema;
    children: React.ReactNode;
    pageInformation: PageInformation;
    actionConfig: Array<{title: string; action: any}>;
}

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
    const {postData, result} = usePostRequest(endpoint, apiNonce, successMessage, errorMessage);
    const [showApiMessage, setApiShowMessage] = useState<boolean>(false);

    const methods = useForm({
        defaultValues: defaultValues,
        resolver: joiResolver(validationSchema),
    });

    const {handleSubmit, getValues} = methods;

    const {isDirty} = methods.formState;

    const handleSubmitRequest = async (formFieldValues) => {
        try {
            if (formFieldValues.hasOwnProperty('createdAt')) {
                formFieldValues.createdAt = format(formFieldValues.createdAt, 'yyyy-MM-dd HH:mm:ss');
            }

            await postData(formFieldValues);
            setApiShowMessage(true);
        } catch (error) {
            console.error(error);
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

import React from 'react';

import {FormProvider, useForm} from 'react-hook-form';
import {joiResolver} from '@hookform/resolvers/joi';

import FormNavigation from '@givewp/components/AdminUI/FormNavigation';
import {Form} from '@givewp/components/AdminUI/FormElements';

/**
 *
 * @unreleased
 */

export interface FormPageProps {
    formId;
    handleSubmitRequest: (formValues) => void;
    defaultValues;
    validationSchema;
    children;
    pageDetails: {
        id: number;
        description: string;
        title: string;
    };
    navigationalOptions: Array<{
        id: number;
        title: string;
    }>;
    actionConfig: Array<{title: string; action: any}>;
}

export default function FormPage({
    formId,
    handleSubmitRequest,
    defaultValues,
    validationSchema,
    pageDetails,
    navigationalOptions,
    children,
    actionConfig,
}: FormPageProps) {
    const methods = useForm({
        defaultValues: defaultValues,
        resolver: joiResolver(validationSchema),
    });

    const {handleSubmit} = methods;

    const {isDirty} = methods.formState;

    return (
        <FormProvider {...methods}>
            <FormNavigation
                pageId={pageDetails.id}
                pageTitle={pageDetails.title}
                pageDescription={pageDetails.description}
                navigationalOptions={navigationalOptions}
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

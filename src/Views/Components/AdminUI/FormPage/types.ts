
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

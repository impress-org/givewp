export interface PageInformation {
    id: number;
    description: string;
    title: string;
}

export interface FormPageProps {
    formId;
    endpoint: string;
    defaultValues;
    validationSchema;
    children;
    pageInformation: PageInformation;
    actionConfig: Array<{title: string; action: any}>;
}

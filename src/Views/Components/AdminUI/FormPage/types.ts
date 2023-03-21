export interface FormPageProps {
    formId;
    endpoint: string;
    defaultValues;
    validationSchema;
    children;
    pageInformation: {
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

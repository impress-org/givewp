export interface FormPage {
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

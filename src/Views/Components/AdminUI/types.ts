export interface FormPage {
    formId;
    handleSubmitRequest: (formValues) => void;
    defaultValues;
    validationSchema;
    children;
    pageDetails:{
        id: number,
        description: string;
        title: string;
    }
    navigationalOptions: Array<{
        id: number
        title: string
    }>
}

export interface FormNavigation {
    navigationalOptions: Array<{id: number; title: string}>;
    onSubmit: () => void;
    pageDescription: string;
    pageId: number;
    pageTitle:string;
}

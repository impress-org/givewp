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
}

export interface FormNavigation {
    navigationalOptions: Array<{id: number; title: string}>;
    onSubmit: () => void;
    pageDescription: string;
    pageId: number;
    pageTitle: string;
}

export interface Button {
    variant: 'primary' | 'secondary' | 'danger';
    size: 'small' | 'large';
    type: 'button' | 'reset' | 'submit';
    children: React.ReactNode;

    onClick?: React.MouseEventHandler<HTMLButtonElement>;
    disabled?: boolean;
    classname?: 'string';
}

export interface Form {
    children: React.ReactNode;
    onSubmit: React.FormEventHandler<HTMLFormElement>;
    id: string;
}

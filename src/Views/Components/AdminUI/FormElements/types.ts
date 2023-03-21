export type FormElementProps = {
    children: React.ReactNode;
    onSubmit: React.FormEventHandler<HTMLFormElement>;
    id: string;
};

export type TextInputFieldProps = {
    name: string;
    type: string;
    placeholder: string;
    label: string;
};

export type CurrencyInputFieldProps = {
    name: string;
    type: string;
    placeholder: string;
    label: string;
    currency: string;
    value?: any;
};

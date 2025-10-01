import { Control, FieldError } from 'react-hook-form';

export interface CampaignFormProps {
    campaignsWithForms: {
        [campaignId: string]: {
            title: string;
            defaultFormId: string;
            forms: {
                [formId: string]: string;
            };
        };
    };
    campaignIdFieldName: string;
    formIdFieldName: string;
}

export interface SelectOption {
    value: number;
    label: string;
}

export interface SelectFieldProps {
    name: string;
    label: string;
    placeholder: string;
    options: SelectOption[];
    control: Control;
    error?: FieldError;
    isDisabled?: boolean;
    className?: string;
    classNamePrefix?: string;
}

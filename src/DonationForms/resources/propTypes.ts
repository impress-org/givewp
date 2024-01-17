import {Element, Field, Gateway, Group, ReceiptDetail, Section as SectionType, SelectOption} from '@givewp/forms/types';
import {FieldErrors, UseFormRegisterReturn} from 'react-hook-form';
import {FC, FormHTMLAttributes, ReactNode} from 'react';

export interface FieldProps extends Field {
    inputProps: UseFormRegisterReturn;
    Label: FC<FieldLabelProps | {}>;
    ErrorMessage: FC<FieldErrors | {}>;
}

export interface GatewayFieldProps extends FieldProps {
    isTestMode: boolean;
    gateways: Gateway[];
}

export type GatewayOptionProps = {
    inputProps: UseFormRegisterReturn;
    gateway: Gateway;
    defaultChecked: boolean;
    isActive: boolean;
};

export interface SelectableFieldProps extends FieldProps {
    options: Array<SelectOption>;
    description: string;
}

export interface MultiSelectProps extends SelectableFieldProps {
    fieldType: string;
}

export interface CheckboxProps extends FieldProps {
    value: string | number;
    helpText?: string;
}

export interface TextareaProps extends FieldProps {
    description?: string;
    rows: number;
}

export interface FieldHasDescriptionProps extends FieldProps {
    description: string;
}

export interface FileProps extends FieldHasDescriptionProps {
    allowedMimeTypes: string[];
}

export interface DateProps extends Omit<FieldHasDescriptionProps, 'placeholder'> {
    dateFormat: string;
}

export interface PhoneProps extends FieldHasDescriptionProps {
    phoneFormat: string;
}

export interface ElementProps extends Element {}

export interface GroupProps extends Group {
    nodeComponents: {
        [key: string]: FC<FieldProps>;
    };

    nodeProps: {
        [key: string]: FieldProps;
    };
}

export interface HtmlProps extends ElementProps {
    html: string;
}

export interface NameProps extends GroupProps {
    nodeComponents: {
        honorific?: FC<SelectableFieldProps | {}>;
        firstName: FC<FieldProps | {}>;
        lastName: FC<FieldProps | {}>;
    };
}

export interface BillingAddressProps extends GroupProps {
    groupLabel: string;
    nodeComponents: {
        country: FC<Partial<SelectableFieldProps> | {}>;
        address1: FC<FieldProps | {}>;
        address2: FC<FieldProps | {}>;
        city: FC<Partial<FieldProps> | {}>;
        state: FC<FieldProps | {}>;
        zip: FC<Partial<FieldProps> | {}>;
    };
    apiUrl: string;
    stateAlwaysRequired: boolean;
    cityAlwaysRequired: boolean;
    zipAlwaysRequired: boolean;
}

export interface DonationAmountProps extends GroupProps {
    nodeComponents: {
        amount: FC<Partial<AmountProps> | {}>;
        donationType: FC<FieldProps | {}>;
        currency: FC<FieldProps | {}>;
        subscriptionFrequency: FC<FieldProps | {}>;
        subscriptionPeriod: FC<FieldProps | {}>;
        subscriptionInstallments: FC<FieldProps | {}>;
    };
    nodeProps: {
        amount: AmountProps;
        donationType: FieldProps;
        currency: FieldProps;
        subscriptionFrequency: FieldProps;
        subscriptionPeriod: FieldProps;
        subscriptionInstallments: FieldProps;
    };
    subscriptionsEnabled: boolean;
    subscriptionDetailsAreFixed: boolean;
}

export interface AmountProps extends FieldProps {
    levels: number[];
    allowLevels: boolean;
    allowCustomAmount: boolean;
    fixedAmountValue: number;
    messages?: ReactNode;
}

export interface ParagraphProps extends ElementProps {
    content: string;
}

export interface SectionProps {
    section: SectionType;
    hideLabel?: boolean;
    hideDescription?: boolean;
    children: ReactNode;
}

export interface FormProps {
    formProps: FormHTMLAttributes<unknown>;
    children: ReactNode;
    formError: string | null;
    isSubmitting: boolean;
}

export interface FieldErrorProps {
    error: string;
    name: string;
}

export interface FieldLabelProps {
    label: string;
    required: boolean;
}

export enum GoalType {
    AMOUNT = 'amount',
    DONATIONS = 'donations',
    DONORS = 'donors',
}

export interface GoalProps {
    currency: string;
    type: GoalType;
    currentAmount: number;
    currentAmountFormatted: string;
    targetAmount: number;
    targetAmountFormatted: string;
    goalLabel: string;
    progressPercentage: number;
    totalRevenue: number;
    totalRevenueFormatted: string;
    totalCountValue: number;
    totalCountLabel: string;
}

export interface GoalAchievedProps {
    goalAchievedMessage: string;
}

export interface HeaderProps {
    Title: FC<HeaderTitleProps | {}>;
    Description: FC<HeaderDescriptionProps | {}>;
    Goal: FC<GoalProps | {}>;
}

export interface HeaderDescriptionProps {
    text: string;
}

export interface HeaderTitleProps {
    text: string;
}

export interface NodeWrapperProps {
    nodeType: keyof typeof window.givewp.form.templates & string;
    // TODO: make type work as this keyof (keyof FormDesign) & string
    type: string;
    name?: string;
    htmlTag?: keyof JSX.IntrinsicElements;
    children: ReactNode;
}

export interface DonationReceiptProps {
    heading: string;
    description: string;
    donorDashboardUrl: string;
    pdfReceiptLink: string;
    donorDetails: ReceiptDetail[];
    donationDetails: ReceiptDetail[];
    subscriptionDetails: ReceiptDetail[];
    additionalDetails: ReceiptDetail[];
}

export interface ConsentProps extends FieldProps {
    useGlobalSettings: boolean;
    checkboxLabel: string;
    displayType: string;
    linkUrl: string;
    linkText: string;
    modalHeading: string;
    modalAcceptanceText: string;
    agreementText: string;
}

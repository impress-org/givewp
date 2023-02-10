import {Element, Field, Gateway, Group, ReceiptDetail, Section as SectionType, SelectOption} from '@givewp/forms/types';
import {FieldErrors, UseFormRegisterReturn} from 'react-hook-form';
import {FC, FormHTMLAttributes, ReactNode} from 'react';

export interface FieldProps extends Field {
    inputProps: UseFormRegisterReturn;
    Label: FC<FieldLabelProps | {}>;
    ErrorMessage: FC<FieldErrors | {}>;
}

export interface GatewayFieldProps extends FieldProps {
    gateways: Gateway[];
}

export type GatewayOptionProps = {
    inputProps: UseFormRegisterReturn;
    gateway: Gateway;
    index: number;
};

export interface SelectFieldProps extends FieldProps {
    options: Array<SelectOption>;
}

export interface ElementProps extends Element {}

export interface GroupProps extends Group {
    fields: {
        [key: string]: FC<FieldProps>;
    };
}

export interface HtmlProps extends ElementProps {
    html: string;
}

export interface NameProps extends GroupProps {
    fields: {
        honorific?: FC<SelectFieldProps | {}>;
        firstName: FC<FieldProps | {}>;
        lastName: FC<FieldProps | {}>;
    };
}

export interface DonationAmountProps extends GroupProps {
    fields: {
        amount: FC<AmountProps | {}>;
        donationType: FC<FieldProps | {}>;
        currency: FC<FieldProps | {}>;
        frequency: FC<FieldProps | {}>;
        period: FC<FieldProps | {}>;
        installments: FC<FieldProps | {}>;
    };
}

export interface AmountProps extends FieldProps {
    levels: Number[];
    allowLevels: boolean;
    allowCustomAmount: boolean;

    fixedAmountValue: number;
}

export interface ParagraphProps extends ElementProps {
    content: string;
}

export interface SectionProps {
    section: SectionType;
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
    donorDetails: ReceiptDetail[];
    donationDetails: ReceiptDetail[];
    subscriptionDetails: ReceiptDetail[];
    additionalDetails: ReceiptDetail[];
}

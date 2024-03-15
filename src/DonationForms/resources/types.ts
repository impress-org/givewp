import type {FC} from 'react';
import {FormSettings} from '@givewp/form-builder/types';

/**
 * Used for a single currency. The amount is an integer in the smallest unit of the currency.
 */
export interface Currency {
    /**
     * Amount as an integer in the smallest unit of the currency.
     */
    amount: bigint;

    /**
     * 3-Character Currency code (e.g. USD, EUR, GBP, etc.)
     */
    currency: string;
}

/**
 * @since 3.0.0
 */
export type CurrencySwitcherSetting = {
    id: string;
    exchangeRate: number;
    gateways: string[];
    exchangeRateFractionDigits: number;
};

export interface FormData {
    honorific?: string;
    firstName: string;
    lastName?: string;
    email: string;
    amount: number;
    company?: string;
}

export interface FormServerExports {
    registeredGateways: RegisteredGateway[];
    form: Form;
    attributes: object;
    donateUrl: string;
    validateUrl: string;
    authUrl: string;
    inlineRedirectRoutes: string[];
    previewMode: boolean;
}

export interface ReceiptDetail {
    label: string;
    value: any;
}

export interface DonationConfirmationReceiptServerExports {
    receipt: {
        settings: {
            heading: string;
            description: string;
            currency: string;
            donorDashboardUrl: string;
            pdfReceiptLink: string;
        };
        donorDetails: ReceiptDetail[];
        donationDetails: ReceiptDetail[];
        eventTicketsDetails: ReceiptDetail[];
        subscriptionDetails: ReceiptDetail[];
        additionalDetails: ReceiptDetail[];
    };
}

export interface GatewaySettings {
    label: string;
}

export interface RegisteredGateway {
    /**
     * The gateway ID. Must be the same as the back-end
     */
    id: string;

    /**
     * The gateway label
     */
    label?: string;

    /**
     * Settings for the gateway as sent from the back-end
     */
    settings?: GatewaySettings;

    /**
     * Whether the gateway supports recurring donations
     */
    supportsSubscriptions?: boolean;
}

export interface Gateway extends RegisteredGateway {
    /**
     * Initialize function for the gateway. The settings are passed to the gateway
     * from the server. This is called once before the form is rendered.
     */
    initialize?(): void;

    /**
     * The component to render when the gateway is selected
     */
    Fields: FC;

    /**
     * A hook before the form is submitted.
     */
    beforeCreatePayment?(values: FormData): Promise<object> | Error;

    /**
     * A hook after the form is submitted.
     */
    afterCreatePayment?(response: FormResponse): Promise<void> | Error;
}

export interface BasicCondition {
    type: 'basic';
    logicalOperator: 'and' | 'or';
    field: string;
    comparisonOperator: '==' | '!=' | '>' | '<' | '>=' | '<=' | 'contains' | 'not_contains';
    value: any;
}

export interface NestedCondition {
    type: 'nested';

    boolean: 'and' | 'or';

    conditions: FieldCondition[];
}

export type FieldCondition = BasicCondition | NestedCondition;

export interface Node {
    name: string;
    type: string;
    nodeType: string;
    visibilityConditions: FieldCondition[];
}

export interface Field extends Node {
    nodeType: 'field';
    label: string;
    placeholder: string | null;
    validationRules: {
        [key: string]: any;
    };
    readOnly: boolean;
    defaultValue: any;
    fieldError: string | null;
}

export interface Group extends Node {
    nodeType: 'group';
    nodes: Node[];

    /**
     * Recursively walk the group and its children for all nodes.
     *
     * @since 3.0.0
     */
    walkNodes(callback: (node: Node) => void, filter?: (node: Node) => boolean): void;

    /**
     * Recursively map the group and its children for all nodes.
     *
     * @since 3.0.0
     */
    mapNodes(callback: (node: Node) => void, filter?: (node: Node) => boolean): Node[];

    /**
     * Recursively walk the group and its children for all nodes and reduce to a single value.
     *
     * @since 3.0.0
     */
    reduceNodes(
        callback: (accumulator: any, node: Node) => any,
        initialValue: any,
        filter?: (node: Node) => boolean
    ): any;
}

export interface Element extends Node {
    nodeType: 'element';
}

export interface Section extends Group {
    label: string;
    description: string;
}

/**
 * @since 3.1.0
 */
export interface FormGoal {
    type: string;
    label: string;
    enabled: boolean;
    show: boolean;
    typeIsCount: boolean;
    typeIsMoney: boolean;
    currentAmount: number;
    targetAmount: number;
    progressPercentage: number;
    isAchieved: boolean;
}

export interface FormColors {
    primaryColor: string;
    secondaryColor: string;
}

export type RequireAtLeastOne<T> = {
    [K in keyof T]-?: Required<Pick<T, K>> & Partial<Pick<T, Exclude<keyof T, K>>>
}[keyof T]

export interface Form extends Group {
    currency: string;
    settings: FormSettings;
    stats: {
        totalRevenue: number;
        totalRevenueFormatted: string;
        totalCountValue: number;
        totalCountLabel: string;
    };
    goal: FormGoal;
    design?: {
        id: string;
        name: string;
        isMultiStep: boolean;
        includeHeaderInMultiStep: boolean;
    };
    nodes: Section[];
    currencySwitcherSettings?: CurrencySwitcherSetting[];
    currencySwitcherMessage?: string;
}

export function isField(node: Node): node is Field {
    return node.nodeType === 'field';
}

export function isElement(node: Node): node is Element {
    return node.nodeType === 'element';
}

export function isGroup(node: Node): node is Group {
    return node.nodeType === 'group';
}

export function isSection(node: Node): node is Section {
    return node.nodeType === 'section';
}

export interface SelectOption {
    label: string;
    value: string;
    disabled?: boolean;
}

export interface RadioOption {
    label: string;
    value: string;
}

interface FormResponse {
    type: string;
    data: any;
}

type FormResponseValidationError = {
    [key: string]: string;
};

type FormResponseGatewayError = {
    gateway_error: string;
};

interface FormResponseValidationErrors extends FormResponse {
    type: 'validation_error';
    data: {
        errors: {
            errors: FormResponseValidationError[];
        };
    };
}

interface FormResponseGatewayErrors extends FormResponse {
    type: 'gateway_error';
    data: {
        errors: {
            errors: FormResponseGatewayError[];
        };
    };
}

interface FormResponseRedirect extends FormResponse {
    type: 'redirect';
    data: {
        redirectUrl: string;
    };
}

export function isFormResponseRedirect(response: FormResponse): response is FormResponseRedirect {
    return (response as FormResponseRedirect).type === 'redirect';
}

export function isFormResponseValidationError(response: FormResponse): response is FormResponseValidationErrors {
    return (
        (response as FormResponseValidationErrors).type === 'validation_error' ||
        (response as FormResponseValidationErrors).data?.errors != undefined
    );
}

export function isFormResponseGatewayError(response: FormResponse): response is FormResponseGatewayErrors {
    return (response as FormResponseGatewayErrors).type === 'gateway_error';
}

export function isResponseRedirected(response: Response): response is Response {
    return (response as Response).redirected === true;
}

export function isDonationTypeSubscription(donationType: string): boolean {
    return donationType === 'subscription';
}

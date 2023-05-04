import {ReactElement} from 'react';

/**
 * @unreleased
 */
export type FormInputs = {
    FORM_ERROR: string;
    amount: number;
    firstName: string;
    lastName: string;
    email: string;
    gatewayId: string;
};

/**
 * @unreleased
 */
export type StepObject = {
    id: number;
    title: string;
    description: string;
    element: ReactElement;
    fields: string[];
};
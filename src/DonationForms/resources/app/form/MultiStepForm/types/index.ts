import type {ReactElement} from 'react';
import {Section} from '@givewp/forms/types';

/**
 * @since 0.4.0
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
 * @since 0.4.0
 */
export type StepObject = {
    id: number;
    title: string;
    description: string;
    element: ReactElement;
    fields: string[];
    visibilityConditions: Section['visibilityConditions'];
    isVisible: boolean;
};

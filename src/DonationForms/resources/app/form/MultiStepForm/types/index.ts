import type {ReactElement} from 'react';
import {Section} from '@givewp/forms/types';

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
    visibilityConditions: Section['visibilityConditions'];
    isVisible: boolean;
};

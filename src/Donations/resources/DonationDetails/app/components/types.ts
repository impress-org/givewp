/**
 *
 * @unreleased
 */
import {ReactNode} from 'react';

export interface FormValues {}

export interface FormTemplate {}

export interface ActionContainer {
    label: string;
    type: string;
    value: string | ReactNode;
    toggleModal?: (event) => void;
}

export interface PaymentMethod {}

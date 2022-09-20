import {Element, Field, Group, SelectOption} from '@givewp/forms/types';
import {UseFormRegisterReturn} from 'react-hook-form';

export interface FieldProps extends Field {
    inputProps: UseFormRegisterReturn;
}

export interface SelectFieldProps extends FieldProps {
    options: Array<SelectOption>;
}

export interface ElementProps extends Element {
}

export interface GroupProps extends Group {
    inputProps: {
        [key: string]: UseFormRegisterReturn;
    };
}

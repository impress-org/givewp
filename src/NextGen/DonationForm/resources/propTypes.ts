import {Element, Field, Group, SelectOption} from '@givewp/forms/types';
import {UseFormRegisterReturn} from 'react-hook-form';
import {FC} from 'react';

export interface FieldProps extends Field {
    inputProps: UseFormRegisterReturn;
    Label: FC;
    ErrorMessage: FC;
}

export interface SelectFieldProps extends FieldProps {
    options: Array<SelectOption>;
}

export interface ElementProps extends Element {}

export interface GroupProps extends Group {
    fieldProps: {
        [key: string]: FieldProps;
    };
}

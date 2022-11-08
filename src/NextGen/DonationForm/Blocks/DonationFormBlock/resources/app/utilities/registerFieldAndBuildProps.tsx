import {Field} from '@givewp/forms/types';
import {FieldProps} from '@givewp/forms/propTypes';
import {UseFormReturn} from 'react-hook-form';
import buildRegisterValidationOptions from './buildRegisterValidationOptions';
import {getFieldErrorTemplate, getFieldLabelTemplate} from '../templates';
import getErrorByFieldName from './getErrorByFieldName';

export default function registerFieldAndBuildProps(
    field: Field,
    register: UseFormReturn['register'],
    errors
): FieldProps {
    const Label = getFieldLabelTemplate();
    const ErrorMessage = getFieldErrorTemplate();
    const fieldError = getErrorByFieldName(errors, field.name);

    return {
        ...field,
        inputProps: register(field.name, buildRegisterValidationOptions(field.validationRules)),
        fieldError,
        Label: () => <Label label={field.label} required={field.validationRules.required} />,
        ErrorMessage: () => <ErrorMessage error={fieldError} />,
    };
}

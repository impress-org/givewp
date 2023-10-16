import {Field} from '@givewp/forms/types';
import {FieldProps} from '@givewp/forms/propTypes';
import {UseFormReturn} from 'react-hook-form';
import buildRegisterValidationOptions from './buildRegisterValidationOptions';
import getErrorByFieldName from './getErrorByFieldName';

const formTemplates = window.givewp.form.templates;
const LabelTemplate = formTemplates.layouts.fieldLabel;
const ErrorMessageTemplate = formTemplates.layouts.fieldError;

export default function registerFieldAndBuildProps(
    field: Field,
    register: UseFormReturn['register'],
    errors
): FieldProps {
    const fieldError = getErrorByFieldName(errors, field.name);

    return {
        ...field,
        inputProps: register(field.name, buildRegisterValidationOptions(field.validationRules)),
        fieldError,
        Label: () => <LabelTemplate label={field.label} required={field.validationRules.required} />,
        ErrorMessage: () => <ErrorMessageTemplate error={fieldError} name={field.name} />,
    };
}

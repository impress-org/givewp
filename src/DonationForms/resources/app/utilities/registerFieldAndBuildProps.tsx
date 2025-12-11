import {Field} from '@givewp/forms/types';
import {FieldProps} from '@givewp/forms/propTypes';
import {UseFormReturn} from 'react-hook-form';
import buildRegisterValidationOptions from './buildRegisterValidationOptions';
import getErrorByFieldName from './getErrorByFieldName';

const formTemplates = window.givewp.form.templates;
const LabelTemplate = formTemplates.layouts.fieldLabel;
const ErrorMessageTemplate = formTemplates.layouts.fieldError;

/**
 * @since 4.3.0 include aria-required attribute in all required fields.
 */
export default function registerFieldAndBuildProps(
    field: Field,
    register: UseFormReturn['register'],
    errors
): FieldProps {
    const fieldError = getErrorByFieldName(errors, field.name);
    const validationOptions = buildRegisterValidationOptions(field.validationRules);

    const baseInputProps = register(field.name, validationOptions);

    const inputProps = {
        ...baseInputProps,
        'aria-required': !!field.validationRules?.required ? 'true' : undefined,
    };

    return {
        ...field,
        inputProps: inputProps,
        fieldError,
        Label: () => <LabelTemplate label={field.label} required={field.validationRules?.required} />,
        ErrorMessage: () => <ErrorMessageTemplate error={fieldError} name={field.name} />,
    };
}

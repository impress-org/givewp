import {sprintf, __} from '@wordpress/i18n';
import Joi, {ObjectSchema} from 'joi';

/**
 * @unreleased
 */
const requiredMessage = sprintf(
    /* translators: base error message */
    __('This is a required field', 'give'),
    `{#label}`
);

/**
 * @unreleased
 */
export const getValidationSchema = (schema: Joi.PartialSchemaMap): ObjectSchema => {
    return Joi.object(schema).messages({
        'string.base': requiredMessage,
        'string.empty': requiredMessage,
        'any.required': requiredMessage,
        'number.base': requiredMessage,
        'object.base': requiredMessage,
    });
}

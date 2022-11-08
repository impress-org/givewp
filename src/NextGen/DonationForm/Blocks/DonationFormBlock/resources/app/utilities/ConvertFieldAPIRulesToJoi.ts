import Joi, {AnySchema, ObjectSchema} from 'joi';
import {Field, Form, isField} from '@givewp/forms/types';
import {__, sprintf} from '@wordpress/i18n';

const requiredMessage = sprintf(
    /* translators: base error message */
    __('%s is required.', 'give`'),
    `{#label}`
);

export default function getJoiRulesForForm(form: Form): ObjectSchema {
    const joiRules = form.reduceNodes(
        (rules, field: Field) => {
            rules[field.name] = getJoiRulesForField(field);

            return rules;
        },
        {},
        isField
    );

    return Joi.object(joiRules).messages({
        'string.base': requiredMessage,
        'string.empty': requiredMessage,
    });
}

function getJoiRulesForField(field: Field): AnySchema {
    let rules: AnySchema = convertFieldAPIRulesToJoi(field.validationRules);

    if (field.label) {
        rules = rules.label(field.label);
    }

    return rules;
}

function convertFieldAPIRulesToJoi(rules): AnySchema {
    let joiRules;

    if (rules.hasOwnProperty('numeric') || rules.hasOwnProperty('integer')) {
        joiRules = Joi.number();

        if (rules.hasOwnProperty('integer')) {
            joiRules = joiRules.integer();
        }
    } else if (rules.hasOwnProperty('boolean')) {
        joiRules = Joi.boolean();
    } else {
        joiRules = Joi.string();

        if (rules.hasOwnProperty('email')) {
            joiRules = joiRules.email({tlds: false});
        }

        if (rules.hasOwnProperty('alpha')) {
            joiRules = joiRules.alpha();
        }

        if (rules.hasOwnProperty('alphanum')) {
            joiRules = joiRules.alphanum();
        }
    }

    if (rules.hasOwnProperty('number') || !rules.hasOwnProperty('boolean')) {
        if (rules.hasOwnProperty('min')) {
            joiRules = joiRules.min(rules.min);
        }

        if (rules.hasOwnProperty('max')) {
            joiRules = joiRules.max(rules.max);
        }
    }

    if (rules.required) {
        joiRules = joiRules.required();
    } else {
        joiRules = joiRules.optional().allow('', null);
    }

    return joiRules;
}

import Joi, {AnySchema, ObjectSchema} from 'joi';
import {BasicCondition, Field, Form, isField} from '@givewp/forms/types';
import {__, sprintf} from '@wordpress/i18n';
import conditionOperatorFunctions from '@givewp/forms/app/utilities/conditionOperatorFunctions';

/**
 * @since 3.0.0
 */
const requiredMessage = sprintf(
    /* translators: base error message */
    __('This is a required field', 'give'),
    `{#label}`
);

/**
 * @since 3.0.0
 */
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
        'any.required': requiredMessage,
        'number.base': requiredMessage,
        'object.base': requiredMessage,
    });
}

/**
 * @since 3.0.0
 */
function getJoiRulesForField(field: Field): AnySchema {
    let rules: AnySchema = convertFieldAPIRulesToJoi(field.validationRules);

    if (field.label) {
        rules = rules.label(field.label);
    }

    return rules;
}

/**
 * @since 3.0.0
 */
function convertFieldAPIRulesToJoi(rules): AnySchema {
    let joiRules;

    if (Object.keys(rules).length === 0) {
        return Joi.any();
    }

    if (rules.hasOwnProperty('numeric') || rules.hasOwnProperty('integer')) {
        joiRules = Joi.number();

        if (rules.hasOwnProperty('integer')) {
            joiRules = joiRules.integer();
        }
    } else if (rules.hasOwnProperty('boolean')) {
        joiRules = Joi.boolean();
    } else if (rules.hasOwnProperty('array')) {
        joiRules = Joi.array();
    } else if (rules.hasOwnProperty('file')) {
        joiRules = Joi.object();
    } else if (rules.hasOwnProperty('dateTime')) {
        joiRules = Joi.date();
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
        if (rules.hasOwnProperty('excludeUnless')) {
            /**
             * This applies requirements to a field if the field is not being excluded by its conditions. It only
             * supports basic conditions at this time, but could be expanded to support more complex conditions.
             *
             * Note that this unsets the value if the field conditions are not met.
             */
            joiRules = joiRules.custom((value, helpers) => {
                const formValues = helpers.state.ancestors[0];

                const passesConditions = rules.excludeUnless.every((condition: BasicCondition) => {
                    return conditionOperatorFunctions[condition.comparisonOperator](
                        formValues[condition.field],
                        condition.value
                    );
                });

                if (passesConditions && (value === '' || value === null)) {
                    return helpers.error('required');
                } else if (!passesConditions) {
                    return undefined;
                }

                return value;
            }, 'exclude unless validation');
        } else {
            joiRules = joiRules.required();
        }
    } else {
        joiRules = joiRules.optional().allow('', null);
    }

    joiRules = getJoiRulesForAmountField(rules, joiRules);

    return joiRules;
}

/**
 * @since 3.0.0
 */
function getJoiRulesForAmountField(rules, joiRules): AnySchema {
    if (rules.hasOwnProperty('donationType')) {
        joiRules = Joi.allow('single', 'subscription').only().required();
    }

    if (rules.hasOwnProperty('subscriptionPeriod')) {
        joiRules = Joi.when('donationType', {
            is: 'subscription',
            then: Joi.allow('day', 'week', 'quarter', 'month', 'year').only().required(),
            otherwise: Joi.optional(),
        });
    }

    if (rules.hasOwnProperty('subscriptionFrequency')) {
        joiRules = Joi.when('donationType', {
            is: 'subscription',
            then: Joi.number().integer().required(),
            otherwise: Joi.optional(),
        });
    }

    if (rules.hasOwnProperty('subscriptionInstallments')) {
        joiRules = Joi.when('donationType', {
            is: 'subscription',
            then: Joi.number().integer().required(),
            otherwise: Joi.optional(),
        });
    }

    return joiRules;
}

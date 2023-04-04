import Joi from 'joi';

/**
 *
 * @unreleased
 */

export const validationSchema = Joi.object().keys({
    id: Joi.number(),
    amount: Joi.number(),
    feeAmountRecovered: Joi.number(),
    createdAt: Joi.date(),
    status: Joi.string(),
    formId: Joi.number(),
    donorId: Joi.string(),
    address1: Joi.string(),
    address2: Joi.string(),
    city: Joi.string(),
    state: Joi.string(),
    zip: Joi.string(),
});

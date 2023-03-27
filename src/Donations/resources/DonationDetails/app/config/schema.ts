import Joi from 'joi';

/**
 *
 * @unreleased
 */

export const validationSchema = Joi.object().keys({
    id: Joi.number(),
    amount: Joi.number(),
    feeAmountRecovered: Joi.number(),
    createdAt: Joi.string(),
    status: Joi.string(),
    form: Joi.number(),
});

import Joi from 'joi';

/**
 *
 * @unreleased
 */

export const validationSchema = Joi.object().keys({
    amount: Joi.string(),
    feeAmountRecovered: Joi.string(),
    createdAt: Joi.string(),
    status: Joi.string(),
    form: Joi.number(),
});

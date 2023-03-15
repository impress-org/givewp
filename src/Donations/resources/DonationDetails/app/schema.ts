import Joi from 'joi';

/**
 *
 * @unreleased
 */

export const validationSchema = Joi.object().keys({
    totalDonation: Joi.string(),
    feeAmount: Joi.string(),
    createdAt: Joi.string(),
    status: Joi.string(),
    form: Joi.number(),
});

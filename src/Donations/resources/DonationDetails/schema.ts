import Joi from 'joi';

/**
 *
 * @unreleased
 */
export const validationSchema = Joi.object().keys({
    test: Joi.string().min(1).max(5).required(),
});

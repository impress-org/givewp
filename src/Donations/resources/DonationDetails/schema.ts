import Joi from 'joi';

/**
 *
 * @unreleased
 */

export const validationSchema = Joi.object().keys({
    totalDonation: Joi.string(),
    feeAmount: Joi.string(),
    createdAt: Joi.string(),
    status: Joi.valid(
        'publish' ||
            'pending' ||
            'processing' ||
            'refunded' ||
            'revoked' ||
            'failed' ||
            'cancelled' ||
            'preApproved' ||
            'abandoned' ||
            'preApproval'
    ),
});

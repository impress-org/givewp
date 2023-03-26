<?php

namespace Give\Donations\Endpoints\DonationDetailsAttributes\PaymentInformation;

use Give\Donations\Endpoints\DonationDetailsAttributes\UpdateDonationAttribute;
use Give\Donations\Models\Donation;
use WP_Error;

/**
 * Class FormIdAttribute
 *
 * @unreleased
 */
class FormIdAttribute extends UpdateDonationAttribute
{
    /**
     * @inheritDoc
     */
    public static function getId(): string
    {
        return 'formId';
    }

    /**
     * @inheritDoc
     */
    public static function getDefinition(): array
    {
        return [
            'type' => 'integer',
            'required' => false,
            'minimum' => 1,
            'sanitize_callback' => 'absint',
            'validate_callback' => function ($param) {
                if (give()->donationForms->getById($param) === null) {
                    return new WP_Error(
                        'form_not_found',
                        __('Form not found.', 'give'),
                        ['status' => 404]
                    );
                }

                return true;
            },
        ];
    }

    /**
     * @inheritDoc
     */
    public static function update($value, Donation $donation): Donation
    {
        $form = give()->donationForms->getById($value);
        $donation->formId = $value;
        $donation->formTitle = $form->title;

        return $donation;
    }
}

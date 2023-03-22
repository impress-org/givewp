<?php

namespace Give\Donations\Endpoints\DonationDetailsAttributes;

use Give\Donations\Models\Donation;
use WP_Error;

/**
 * Class IdAttribute
 *
 * @unreleased
 */
class FormIdAttribute extends DonationDetailsAttribute
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

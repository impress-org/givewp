<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Rules\HoneyPotRule;
use Give\Framework\FieldsAPI\DonationForm;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Honeypot;

/**
 * @since 3.16.2
 */
class AddHoneyPotFieldToDonationForms
{
    /**
     * @since 3.16.2
     * @throws EmptyNameException
     */
    public function __invoke(DonationForm $form): void
    {
        $formNodes = $form->all();
        $lastSection = $form->count() ? $formNodes[$form->count() - 1] : null;
        $honeypotFieldName = apply_filters('givewp_donation_forms_honeypot_field_name', 'donationBirthday');

        if ($lastSection && is_null($form->getNodeByName($honeypotFieldName))) {
            $field = Honeypot::make($honeypotFieldName)
                ->label('Donation Birthday')
                ->scope('honeypot')
                ->showInAdmin(false)
                ->showInReceipt(false)
                ->rules(new HoneyPotRule());

            $lastSection->append($field);
        }
    }
}

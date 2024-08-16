<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Rules\HoneyPotRule;
use Give\Framework\FieldsAPI\DonationForm;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;
use Give\Framework\FieldsAPI\Honeypot;

/**
 * @unreleased
 */
class AddHoneyPotFieldToDonationForms
{
    /**
     * @unreleased
     * @throws EmptyNameException
     */
    public function __invoke(DonationForm $form): void
    {
        $formNodes = $form->all();
        $lastSection = $form->count() ? $formNodes[$form->count() - 1] : null;

        if ($lastSection) {
            $field = Honeypot::make('donationBirthday')
                ->label('Donation Birthday')
                ->scope('honeypot')
                ->showInAdmin(false)
                ->showInReceipt(false)
                ->rules(new HoneyPotRule());

            $lastSection->append($field);
        }
    }
}

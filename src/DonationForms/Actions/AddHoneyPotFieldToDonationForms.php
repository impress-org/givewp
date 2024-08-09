<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Rules\HoneyPotRule;
use Give\FormAPI\Form\Text;
use Give\Framework\FieldsAPI\Date;
use Give\Framework\FieldsAPI\DonationForm;
use Give\Framework\FieldsAPI\Exceptions\EmptyNameException;

/**
 * @unreleased
 */
class AddHoneyPotFieldToDonationForms {
    /**
     * @unreleased
     * @throws EmptyNameException
     */
    public function __invoke(DonationForm $form)
    {
        $formNodes = $form->all();
        $lastSection = $form->count() ? $formNodes[$form->count() - 1] : null;

        $field = Date::make('birthday')
            ->label('Birthday')
            ->scope('honeypot')
            ->showInAdmin(false)
            ->showInReceipt(false)
            ->rules(new HoneyPotRule());

        if ($lastSection) {
            $lastSection->append($field);
        }
    }
}

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
     * @since 3.17.0 added parameter $honeypotFieldName
     * @since 3.16.2
     * @throws EmptyNameException
     */
    public function __invoke(DonationForm $form, string $honeypotFieldName): void
    {
        $formNodes = $form->all();
        $lastSection = $form->count() ? $formNodes[$form->count() - 1] : null;

        if ($lastSection && is_null($form->getNodeByName($honeypotFieldName))) {
            $field = Honeypot::make($honeypotFieldName)
                ->label($this->generateLabelFromFieldName($honeypotFieldName))
                ->scope('honeypot')
                ->showInAdmin(false)
                ->showInReceipt(false)
                ->rules(new HoneyPotRule());

            $lastSection->append($field);
        }
    }

    /**
     * @since 3.17.0
     */
    private function generateLabelFromFieldName(string $honeypotFieldName): string
    {
        return ucwords(trim(implode(" ", preg_split("/(?=[A-Z])/", $honeypotFieldName))));
    }
}

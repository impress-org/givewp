<?php

namespace Give\Tracking\Helpers;

use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class FormBlocks
{
    /**
     * @var DonationForm
     * @unreleased
     */
    protected $form;

    /**
     * @unreleased
     */
    public function __construct(DonationForm $form)
    {
        $this->form = $form;
    }

    /**
     * @unreleased
     */
    public static function formId($formId): self
    {
        return new self(DonationForm::find($formId));
    }

    /**
     * @unreleased
     */
    public function hasBlock(string $blockName): bool
    {
        return (bool) $this->form->blocks->findByName($blockName);
    }
}

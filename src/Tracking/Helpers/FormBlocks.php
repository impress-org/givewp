<?php

namespace Give\Tracking\Helpers;

use Give\DonationForms\Models\DonationForm;

/**
 * @since 3.10.0
 */
class FormBlocks
{
    /**
     * @var DonationForm
     * @since 3.10.0
     */
    protected $form;

    /**
     * @since 3.10.0
     */
    public function __construct(DonationForm $form)
    {
        $this->form = $form;
    }

    /**
     * @since 3.10.0
     */
    public static function formId($formId): self
    {
        return new self(DonationForm::find($formId));
    }

    /**
     * @since 3.10.0
     */
    public function hasBlock(string $blockName): bool
    {
        return (bool) $this->form->blocks->findByName($blockName);
    }
}

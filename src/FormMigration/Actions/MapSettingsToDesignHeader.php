<?php

namespace Give\FormMigration\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\FormMigration\DataTransferObjects\DesignHeaderSettings;
use Give\FormMigration\DataTransferObjects\DonationSummarySettings;
use Give\Framework\Blocks\BlockCollection;

class MapSettingsToDesignHeader
{
    /**
     * @var DonationForm
     */
    protected $form;

    public function __construct(DonationForm $form)
    {
        $this->form = $form;
    }

    public static function make(DonationForm $form): self
    {
        return new self($form);
    }

    public function __invoke(DesignHeaderSettings $settings)
    {
        $this->form->settings->showHeader = $settings->isEnabled();

        $this->form->settings->showHeading = $settings->hasHeading();
        $this->form->settings->heading = $settings->getHeading();

        $this->form->settings->showDescription = $settings->hasDescription();
        $this->form->settings->description = $settings->getDescription();
    }
}

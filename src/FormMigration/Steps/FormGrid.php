<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Concerns\Blocks\BlockFactory;
use Give\FormMigration\Contracts\FormMigrationStep;

class FormGrid extends FormMigrationStep
{
    public function process()
    {
        $this->formV3->settings->formGridCustomize = $this->formV2->isFormGridCustomized();
        $this->formV3->settings->formGridRedirectUrl = $this->formV2->getFormGridRedirectUrl();
        $this->formV3->settings->formGridDonateButtonText = $this->formV2->getFormGridDonateButtonText();
        $this->formV3->settings->formGridHideDocumentationLink = $this->formV2->isFormGridCustomized();
    }
}

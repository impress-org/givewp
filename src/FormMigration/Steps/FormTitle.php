<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

class FormTitle extends FormMigrationStep
{
    public function process()
    {
        $formTitle = sprintf('%s [v3]', $this->formV2->title);
        $this->formV3->title = $formTitle;
        $this->formV3->settings->formTitle = $formTitle;
    }
}

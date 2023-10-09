<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

class FormTitle extends FormMigrationStep
{
    public function process()
    {
        $this->formV3->title = $this->formV2->title;
        $this->formV3->settings->formTitle = $this->formV2->title;
    }
}

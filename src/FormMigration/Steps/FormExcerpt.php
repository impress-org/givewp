<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @unreleased
 */
class FormExcerpt extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function process()
    {
        $this->formV3->settings->formExcerpt = get_the_excerpt($this->formV2->id);
    }
}

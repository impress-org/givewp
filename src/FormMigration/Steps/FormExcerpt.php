<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.7.0
 */
class FormExcerpt extends FormMigrationStep
{
    /**
     * @since 3.7.0
     */
    public function process()
    {
        $this->formV3->settings->formExcerpt = get_the_excerpt($this->formV2->id);
    }
}

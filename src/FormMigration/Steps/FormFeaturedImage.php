<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.5.0
 */
class FormFeaturedImage extends FormMigrationStep
{
    /**
     * @since 3.5.0
     */
    public function process()
    {
        if ($formV2FeaturedImage = $this->formV2->getFormFeaturedImage()) {
            $this->formV3->settings->designSettingsImageUrl = $formV2FeaturedImage;
            if ('sequoia' === $this->formV2->getFormTemplate()) {
                $this->formV3->settings->designSettingsImageStyle = 'center';
            }
        }
    }
}

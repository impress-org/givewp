<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.0.0-rc.6
 */
class FormMeta extends FormMigrationStep
{
    /**
     * @since 3.0.0-rc.6
     */
    public function process()
    {
        $oldFormId = $this->formV2->id;
        $newFormId = $this->formV3->id;

        $oldFormMeta = give()->form_meta->get_meta($oldFormId);

        if ($oldFormMeta && is_array($oldFormMeta)) {
            foreach ($oldFormMeta as $oldFormMetaKey => $oldFormMetaValue) {
                $oldFormMetaValue = is_array($oldFormMetaValue) && count($oldFormMetaValue) === 1 ? $oldFormMetaValue[0] : $oldFormMetaValue;
                give()->form_meta->update_meta($newFormId, $oldFormMetaKey, $oldFormMetaValue);
            }
        }
    }
}

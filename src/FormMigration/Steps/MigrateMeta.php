<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

class MigrateMeta extends FormMigrationStep
{
    public function process()
    {
        $oldFormId = $this->formV2->id;
        $newFormId = $this->formV3->id;
        give_update_meta( $newFormId, 'migratedFormId', $oldFormId );
    }
}

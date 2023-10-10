<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Database\DB;

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

        $formMetaTable = DB::prefix('give_formmeta');

        DB::query(
            "
                INSERT INTO $formMetaTable (form_id, meta_key, meta_value)
                SELECT
                    $newFormId,
                    meta_key,
                    meta_value
                FROM
                    $formMetaTable
                WHERE
                    form_id = $oldFormId
                "
        );
    }
}

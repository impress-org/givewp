<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Database\DB;

/**
 * @since 3.0.0
 */
class FormMeta extends FormMigrationStep
{
    /**
     * @since 3.0.0
     */
    public function process()
    {
        $oldFormId = $this->formV2->id;
        $newFormId = $this->formV3->id;

        $formMetaTable = DB::prefix('give_formmeta');

        DB::query(
            DB::prepare(
                "
                    INSERT INTO $formMetaTable (form_id, meta_key, meta_value)
                    SELECT
                        %d,
                        meta_key,
                        meta_value
                    FROM
                        $formMetaTable
                    WHERE
                        form_id = %d
                        AND meta_key NOT IN (
                            SELECT meta_key FROM $formMetaTable WHERE form_id = %d
                        )
                    ",
                $newFormId,
                $oldFormId,
                $newFormId
            )
        );
    }
}

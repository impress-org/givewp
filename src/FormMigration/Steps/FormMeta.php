<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Log\Log;

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

        DB::beginTransaction();

        try {
            $insertQuery = DB::query(
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

            if (!$insertQuery) {
                throw new Exception('Failed running form meta migration.');
            }
        } catch (Exception $exception) {
            DB::rollback();
            Log::error($exception->getMessage());
        }

        DB::commit();
    }
}

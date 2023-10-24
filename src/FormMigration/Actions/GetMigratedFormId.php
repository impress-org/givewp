<?php

namespace Give\FormMigration\Actions;

use Give\Framework\Database\DB;

class GetMigratedFormId
{
    public function __invoke(int $v2FormId)
    {
        global $wpdb;
        return DB::get_var(
            DB::prepare(
                "
                    SELECT `form_id`
                    FROM `{$wpdb->prefix}give_formmeta`
                    JOIN `{$wpdb->posts}`
                        ON `{$wpdb->posts}`.`ID` = `{$wpdb->prefix}give_formmeta`.`form_id`
                    WHERE `post_status` != 'trash'
                      AND `meta_key` = 'migratedFormId'
                      AND `meta_value` = %d",
                $v2FormId
            )
        );
    }
}

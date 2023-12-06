<?php

namespace Give\DonationForms\Migrations;

use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @since 3.2.0
 */
class CleanMultipleSlashesOnDB extends Migration
{
    /**
     * @since 3.2.0
     */
    public function run()
    {
        $formMetaTable = DB::prefix('give_formmeta');

        /**
         * For some reason, these 24 backslashes used in the SQL instruction became 12 when we passed it to the $sql var,
         * and became 6 when it runs on the database, which represents a sequence of only 3 backslashes as we need 2 of
         * them to escape just 1 as you can check here: https://dev.mysql.com/doc/refman/8.0/en/string-literals.html
         */
        $sql = DB::prepare("SELECT * FROM $formMetaTable as fm
         WHERE fm.meta_key = 'formBuilderSettings' AND fm.meta_value LIKE '%\\\\\\\\\\\\\\\\\\\\\\\\%'");

        $items = DB::get_results($sql);

        foreach ($items as $item) {
            $settings = $this->cleanMultipleSlashes(json_decode($item->meta_value, true, JSON_UNESCAPED_SLASHES));

            DB::table('give_formmeta')
                ->where('form_id', $item->form_id)
                ->where('meta_key', DonationFormMetaKeys::SETTINGS()->getValue())
                ->update([
                    'meta_value' => json_encode($settings),
                ]);
        }
    }

    /**
     * @inheritdoc
     */
    public static function id()
    {
        return 'donation-forms-clean-multiple-slashes-on-db';
    }

    /**
     * @inheritdoc
     */
    public static function title()
    {
        return 'Clean multiple slashes in the formBuilderSettings meta value';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2023-20-11');
    }

    /**
     * @since 3.2.0
     */
    public function cleanMultipleSlashes($var)
    {
        if (is_array($var)) {
            return array_map([$this, 'cleanMultipleSlashes'], $var);
        } else {
            return is_string($var) ? deslash($var) : $var;
        }
    }
}

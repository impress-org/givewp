<?php

namespace Give\DonationForms\Migrations;

use Give\DonationForms\ValueObjects\DonationFormMetaKeys;
use Give\Framework\Database\DB;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class CleanMultipleSlashesOnDB extends Migration
{
    /**
     * @unreleased
     */
    public function run()
    {
        $formMetaTable = DB::prefix('give_formmeta');

        /**
         * For some reason, these 24 backslashes used in the SQL instruction became 12 when we passed it to the $sql var,
         * and became 6 when it runs on the database, which represents a sequence of only 3 backslashes as we need 2 of
         * them to escape just 1 as you can check here: https://dev.mysql.com/doc/refman/8.0/en/string-literals.html
         */
        $sql = DB::prepare("SELECT form_id FROM $formMetaTable as fm WHERE
        (fm.meta_key = 'formBuilderSettings' AND fm.meta_value LIKE '%\\\\\\\\\\\\\\\\\\\\\\\\%')
        OR
        (fm.meta_key = 'formBuilderFields' AND fm.meta_value LIKE '%\\\\\\\\\\\\\\\\\\\\\\\\%')");

        $formIds = DB::get_results($sql, ARRAY_A);

        foreach ($formIds as $formId) {
            $formId = (int)$formId['form_id'];

            $settings = Give()->form_meta->get_meta($formId, 'formBuilderSettings', true);
            $blocks = Give()->form_meta->get_meta($formId, 'formBuilderFields', true);

            $settings = json_decode($settings, true, JSON_UNESCAPED_SLASHES);
            $blocks = json_decode($blocks, true, JSON_UNESCAPED_SLASHES);

            $settings = $this->cleanMultipleSlashes($settings);
            $blocks = $this->cleanMultipleSlashes($blocks);


            DB::table('give_formmeta')
                ->where('form_id', $formId)
                ->where('meta_key', DonationFormMetaKeys::SETTINGS()->getValue())
                ->update([
                    'meta_value' => json_encode($settings),
                ]);

            DB::table('give_formmeta')
                ->where('form_id', $formId)
                ->where('meta_key', DonationFormMetaKeys::FIELDS()->getValue())
                ->update([
                    'meta_value' => json_encode($blocks),
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
        return 'Clean multiple slashes in the formBuilderSettings and formBuilderFields meta values';
    }

    /**
     * @inheritdoc
     */
    public static function timestamp()
    {
        return strtotime('2023-20-11');
    }

    /**
     * @unreleased
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

<?php

namespace Give\DonationForms\Migrations;

use Give\DonationForms\Actions\SanitizeDonationFormPreviewRequest;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Properties\FormSettings;
use Give\Framework\Blocks\BlockCollection;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Migrations\Contracts\Migration;

/**
 * @unreleased
 */
class CleanMultipleSlashesOnDB extends Migration
{

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function run()
    {
        global $wpdb;

        $formMetaTable = $wpdb->prefix . 'give_formmeta';

        $sql = $wpdb->prepare("SELECT form_id FROM $formMetaTable as fm WHERE
        (fm.meta_key = 'formBuilderSettings' AND fm.meta_value LIKE '%\\\\\\\\\\\\\\\\\\\\\\\\%')
        OR
        (fm.meta_key = 'formBuilderFields' AND fm.meta_value LIKE '%\\\\\\\\\\\\\\\\\\\\\\\\%')");

        $formIds = $wpdb->get_results($sql, ARRAY_A);

        foreach ($formIds as $formId) {
            $form = DonationForm::find((int)$formId['form_id']);
            $settings = (new SanitizeDonationFormPreviewRequest())($form->settings->toArray());
            $blocks = (new SanitizeDonationFormPreviewRequest())($form->blocks->toArray());
            $form->settings->emailTemplateOptions = FormSettings::fromArray($settings);
            $form->blocks = BlockCollection::make($blocks);
            $form->save();
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
}

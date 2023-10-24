<?php

namespace Give\FormMigration\Steps\FormTemplate;

use Give\FormMigration\Concerns\Blocks\BlockFactory;;
use Give\FormMigration\Contracts\FormMigrationStep;

class LegacyTemplateSettings extends FormMigrationStep
{
    public function canHandle(): bool
    {
        return 'legacy' === $this->formV2->getFormTemplate();
    }

    public function process()
    {
        [
            'display_settings' => $displaySettings,
        ] = $this->formV2->getFormTemplateSettings();

        $this->displaySettings($displaySettings);
    }

    protected function displaySettings($settings)
    {
        [
            'display_style' => $displayStyle, // 'buttons',
            'payment_display' => $paymentDisplay, // 'onpage',
            'reveal_label' => $revealLabel, // '',
            'checkout_label' => $checkoutLabel, // 'Donate Now',
            'form_floating_labels' => $floatingLabels, // 'global',
            'display_content' => $displayContent, // 'disabled',
            'content_placement' => $contentPlacement, // 'give_pre_form',
            'form_content' => $formContent, // '',
        ] = $settings;

        // @note `display_style`` is not supported in v3 forms (defers to the Form Design).

        // @note `payment_display`, `reveal_label` are not supported in v3 forms (defers to the Form Design).

        // @note `checkout_label` is not supported in v3 forms (defers to the Form Design).

        // @note `form_floating_labels` is not supported in v3 forms (defers to the Form Design).

        if(give_is_setting_enabled($displayContent)) {

            $formContentSection = BlockFactory::section();
            $formContentSection->innerBlocks->append(BlockFactory::paragraph($formContent));

            if('give_pre_form' === $contentPlacement) {
                $this->fieldBlocks->prepend($formContentSection);
            } else {
                $this->fieldBlocks->append($formContentSection);
            }
        }
    }
}

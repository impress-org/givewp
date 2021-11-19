<?php

namespace Give\Form\Template;

use Give\Form\Template;
use Give\Helpers\Form\Utils;

/**
 * Class LegacyFormSettingCompatibility
 *
 * @package Give\Form\Template
 * @since 2.7.0
 */
class LegacyFormSettingCompatibility
{
    /**
     * Name of key which we are using in field description to connect template setting with legacy setting.
     *
     * @var string
     */
    public static $key = 'mapToLegacySetting';

    /**
     * Map legacy setting key name to template function/property from which you will get donation form configuration about different features.
     *
     * @var array $mapToTemplateProperty Form settings default values for form template.
     */
    private $mapToTemplateProperty = [
        '_give_display_style' => 'donationFormLevelsStyle',
        '_give_payment_display' => 'donationFormStyle',
        '_give_form_floating_labels' => 'floatingLabelsStyle',
        '_give_reveal_label' => 'getContinueToDonationFormLabel',
        '_give_checkout_label' => 'getDonateNowButtonLabel',
        '_give_display_content' => 'showDonationIntroductionContent',
        '_give_content_placement' => 'getDonationIntroductionContentPosition',
        '_give_form_content' => 'getDonationIntroductionContent',
    ];

    /**
     * @var Template $template
     */
    private $template;

    /**
     * LegacyFormSettingCompatibility constructor.
     *
     * @param Template $template
     */
    public function __construct(Template $template)
    {
        $this->template = $template;
    }

    /**
     * Save legacy settings.
     *
     * Note: we are using this function internally to store legacy form settings when save form template setting.
     *
     * @see src/Helpers/Form/Theme/Theme.php:46  we are using this function in set function.
     * @since 2.7.0
     *
     * @param array $settings
     * @param int   $formId
     */
    public function save($formId, $settings)
    {
        $alreadySavedLegacySettings = [];
        $templateOptions = $this->template->getOptionsConfig();
        $enableDisableValueSet = ['disabled', 'enabled'];

        foreach ($templateOptions as $groupId => $group) {
            foreach ($group['fields'] as $field) {
                // Continue if setting is not map to legacy setting.
                if ( ! isset($field[self::$key])) {
                    continue;
                }

                Give()->form_meta->update_meta($formId, $field[self::$key], $settings[$groupId][$field['id']]);
                $alreadySavedLegacySettings[] = $field[self::$key];
            }
        }

        if ($remainingSettings = array_diff(array_keys($this->mapToTemplateProperty), $alreadySavedLegacySettings)) {
            foreach ($remainingSettings as $metaKey) {
                $value = property_exists($this->template, $this->mapToTemplateProperty[$metaKey]) ?
                    $this->template->{$this->mapToTemplateProperty[$metaKey]} : // Get value from property.
                    $this->template->{$this->mapToTemplateProperty[$metaKey]}(); // Get value from function

                // Convert boolean value to enable and disabled.
                if (is_bool($value)) {
                    $value = $enableDisableValueSet[absint($value)];
                }

                Give()->form_meta->update_meta(
                    $formId,
                    $metaKey,
                    $value
                );
            }
        }
    }

    /**
     * Migrate existing legacy form settings.
     * Note: Only for internal use. This function can be removed or change in future
     *
     * @since 2.7.0
     */
    public static function migrateExistingFormSettings()
    {
        // Only migrate settings for existing form when editing.
        if ( ! isset($_GET['action']) || 'edit' !== give_clean($_GET['action'])) {
            return;
        }

        $formId = absint($_GET['post']);

        if ( ! Utils::isLegacyForm($formId)) {
            return;
        }

        if (Give()->form_meta->get_meta($formId, '_give_legacy_form_template_settings', true)) {
            return;
        }

        $mapToSetting = [
            'display_style' => '_give_display_style',
            'payment_display' => '_give_payment_display',
            'reveal_label' => '_give_reveal_label',
            'checkout_label' => '_give_checkout_label',
            'form_floating_labels' => '_give_form_floating_labels',
            'display_content' => '_give_display_content',
            'content_placement' => '_give_content_placement',
            'form_content' => '_give_form_content',
        ];

        foreach ($mapToSetting as $newSetting => $oldSetting) {
            if ($value = Give()->form_meta->get_meta($formId, $oldSetting, true)) {
                $settings['display_settings'][$newSetting] = $value;
            }
        }

        Give()->form_meta->update_meta($formId, '_give_form_template', 'legacy');
        Give()->form_meta->update_meta($formId, '_give_legacy_form_template_settings', $settings);
    }
}

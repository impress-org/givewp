<?php

namespace Give\Helpers\Form;

use Give\Form\Template\LegacyFormSettingCompatibility;
use Give\Helpers\Form\Template\Utils\Frontend;

/**
 * @since 2.7.0
 */
class Template
{
    /**
     * This function will return selected form template for a specific form.
     *
     * @since 2.7.0
     *
     * @param int $formId Form id. Default value: check explanation in ./Utils.php:103
     *
     * @return string
     */
    public static function getActiveID($formId = null)
    {
        return Give()->form_meta->get_meta($formId ?: Frontend::getFormId(), '_give_form_template', true);
    }

    /**
     * Return saved form template settings
     *
     * @since 2.7.0
     *
     * @param string $templateId
     *
     * @param int    $formId
     *
     * @return array
     */
    public static function getOptions($formId = null, $templateId = '')
    {
        $formId = $formId ?: Frontend::getFormId();
        $template = $templateId ?: Give()->form_meta->get_meta($formId, '_give_form_template', true);
        $settings = Give()->form_meta->get_meta($formId, "_give_{$template}_form_template_settings", true);

        $settings = $settings ?: [];

        // Exit if admin did not have any settings.
        // New donation form does not have any setting saved.
        if ( ! $settings) {
            return $settings;
        }

        /**
         * Backwards compatibility for forms saved before the Donation Summary was introduced.
         * @since 2.17.0
         */
        if ( ! isset($settings['payment_information'])) {
            $settings['payment_information'] = [
                'donation_summary_enabled' => 'disabled', // Disable by default for existing forms.
                'donation_summary_heading' => __('Here\'s what you\'re about to donate:', 'give'),
                'donation_summary_location' => 'give_donation_form_before_submit',
            ];
        }

        // Backward compatibility for migrated settings.
        // 1. "Introduction -> Primary Color" move to "Visual Appearance -> Primary Color"
        // 2. "Payment Amount -> Decimal amounts" move to "Visual Appearance -> Decimal amounts"
        return self::handleOptionsBackwardCompatibility($settings, $template);
    }

    /**
     * Save settings
     *
     * @sinxe 2.7.0
     *
     * @param $formId
     * @param $settings
     *
     * @return bool
     */
    public static function saveOptions($formId, $settings)
    {
        $templateId = Give()->form_meta->get_meta($formId, '_give_form_template', true);
        $template = Give()->templates->getTemplate($templateId);
        $isUpdated = Give()->form_meta->update_meta($formId, "_give_{$templateId}_form_template_settings", $settings);

        /*
         * Below code save legacy setting which connected/mapped to form template setting.
         * Existing form render on basis of these settings if missed then required output will not generate from give_form_shortcode -> give_get_donation_form function.
         *
         * Note: We can remove legacy setting compatibility by returning anything except LegacyFormSettingCompatibility class object.
         */
        $legacySettingHandler = new LegacyFormSettingCompatibility($template);
        $legacySettingHandler->save($formId, $settings);

        return $isUpdated;
    }

    /**
     * @since 2.16.0
     *
     * @since 2.16.2 Accepts parameter "call by value". Pass multiple to arguments to isset to validate whether deprecated settings exist.
     *
     * @param array $settings
     *
     * @param string $template
     *
     * @return array $settings
     */
    public static function handleOptionsBackwardCompatibility($settings, $template)
    {
        if (isset($settings['visual_appearance'])) {
            if (isset($settings['visual_appearance']['decimals_enabled'])) {
                $settings['payment_amount']['decimals_enabled'] = $settings['visual_appearance']['decimals_enabled'];
            }
            $settings['introduction']['primary_color'] = $settings['visual_appearance']['primary_color'];
        } elseif (isset($settings['payment_amount'], $settings['introduction'])) {
            if (isset($settings['payment_amount']['decimals_enabled'])) {
                $settings['visual_appearance']['decimals_enabled'] = $settings['payment_amount']['decimals_enabled'];
            } else {
                $settings['visual_appearance']['decimals_enabled'] = 'disabled';
            }
            if (isset($settings['visual_appearance']['primary_color'])) {
                $settings['visual_appearance']['primary_color'] = $settings['introduction']['primary_color'];
            } else {
                switch ($template) {
                    case 'sequoia':
                        $settings['visual_appearance']['primary_color'] = '#28C77B';
                        break;
                    case 'classic':
                    default:
                        $settings['visual_appearance']['primary_color'] = '#1E8CBE';
                        break;
                }
            }
        }

        return $settings;
    }
}

<?php

namespace Give\Form\Template;

use Give\FormAPI\Section;

/**
 * Class Options
 *
 * @package Give\Form\Template
 * @since   2.7.0
 */
final class Options
{
    /**
     * Theme Options
     *
     * @since 2.7.0
     * @var array
     */
    public $sections = [];

    /**
     * ThemeOptions constructor.
     *
     * @since 2.7.0
     *
     * @param $array
     *
     * @return Options
     */
    public static function fromArray($array)
    {
        $options = new static();

        foreach ($array as $id => $group) {
            $group['id'] = $id;
            $options->sections[] = Section::fromArray($group);
        }

        return $options;
    }

    /**
     * Return array configuration for checkout label setting field.
     *
     * Note: if you want to add an option in template to overwrite "Donate Now" button title then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @return array
     */
    public static function getCheckoutLabelField()
    {
        return [
            'id' => 'checkout_label',
            'name' => __('Submit Button', 'give'),
            'desc' => __('The button label for completing a donation.', 'give'),
            'type' => 'text_medium',
            'attributes' => [
                'placeholder' => __('Donate Now', 'give'),
            ],
            'default' => __('Donate Now', 'give'),
            LegacyFormSettingCompatibility::$key => '_give_checkout_label',
        ];
    }

    /**
     * Return array configuration for display style setting field.
     *
     * Note: if you want to add an option in template to overwrite donation levels style then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @return array
     */
    public static function getDonationLevelsDisplayStyleField()
    {
        return [
            'name' => __('Display Style', 'give'),
            'description' => __('Set how the donations levels will display on the form.', 'give'),
            'id' => 'display_style',
            'type' => 'radio_inline',
            'default' => 'buttons',
            'options' => [
                'buttons' => __('Buttons', 'give'),
                'radios' => __('Radios', 'give'),
                'dropdown' => __('Dropdown', 'give'),
            ],
            'wrapper_class' => 'give-hidden _give_display_style_field',
            LegacyFormSettingCompatibility::$key => '_give_display_style',
        ];
    }

    /**
     * Return array configuration for display options setting field.
     *
     * Note: if you want to add an option in template to overwrite donation form display style then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @param array $displayType
     *
     * @return array
     */
    public static function getDisplayOptionsField($displayType = [])
    {
        return [
            'name' => __('Display Options', 'give'),
            'desc' => sprintf(__('How would you like to display donation information for this form?', 'give'), '#'),
            'id' => 'payment_display',
            'type' => 'radio_inline',
            'options' => array_merge(
                [
                    'onpage' => __('All Fields', 'give'),
                    'button' => __('Button', 'give'),
                ],
                $displayType
            ),
            'wrapper_class' => '_give_payment_display_field',
            'default' => 'onpage',
            LegacyFormSettingCompatibility::$key => '_give_payment_display',
        ];
    }

    /**
     * Return array configuration for continue to donation button label ( reveal label ) setting field.
     *
     * Note: if you want to add an option in template to overwrite reveal_label text then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @return array
     */
    public static function getContinueToDonationFormField()
    {
        return [
            'id' => 'reveal_label',
            'name' => __('Continue Button', 'give'),
            'desc' => __('The button label for displaying the additional payment fields.', 'give'),
            'type' => 'text_small',
            'attributes' => [
                'placeholder' => __('Donate Now', 'give'),
            ],
            'wrapper_class' => '_give_reveal_label_field give-hidden',
            LegacyFormSettingCompatibility::$key => '_give_reveal_label',
        ];
    }

    /**
     * Return array configuration for float labels setting field.
     *
     * Note: if you want to add an option in template to overwrite float labels feature then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @return array
     */
    public static function getFloatLabelsField()
    {
        return [
            'name' => __('Floating Labels', 'give'),
            /* translators: %s: forms http://docs.givewp.com/form-floating-labels */
            'desc' => sprintf(
                __(
                    'Select the <a href="%s" target="_blank">floating labels</a> setting for this GiveWP form. Be aware that if you have the "Disable CSS" option enabled, you will need to style the floating labels yourself.',
                    'give'
                ),
                esc_url('http://docs.givewp.com/form-floating-labels')
            ),
            'id' => 'form_floating_labels',
            'type' => 'radio_inline',
            'options' => [
                'global' => __('Global Option', 'give'),
                'enabled' => __('Enabled', 'give'),
                'disabled' => __('Disabled', 'give'),
            ],
            'default' => 'global',
            LegacyFormSettingCompatibility::$key => '_give_form_floating_labels',
        ];
    }

    /**
     * Return array configuration for display content setting field.
     *
     * Note: if you want to add an option in template to overwrite display content feature then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @return array
     */
    public static function getDisplayContentField()
    {
        return [
            'name' => __('Display Content', 'give'),
            'description' => __('Do you want to add custom content to this form?', 'give'),
            'id' => 'display_content',
            'type' => 'radio_inline',
            'options' => [
                'enabled' => __('Enabled', 'give'),
                'disabled' => __('Disabled', 'give'),
            ],
            'wrapper_class' => '_give_display_content_field',
            'default' => 'disabled',
            LegacyFormSettingCompatibility::$key => '_give_display_content',
        ];
    }

    /**
     * Return array configuration for content placement setting field.
     *
     * Note: if you want to add an option in template to overwrite content placement feature then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @return array
     */
    public static function getContentPlacementField()
    {
        return [
            'name' => __('Content Placement', 'give'),
            'description' => __('This option controls where the content appears within the donation form.', 'give'),
            'id' => 'content_placement',
            'type' => 'radio_inline',
            'options' => apply_filters(
                'give_forms_content_options_select',
                [
                    'give_pre_form' => __('Above fields', 'give'),
                    'give_post_form' => __('Below fields', 'give'),
                ]
            ),
            'wrapper_class' => '_give_content_placement_field give-hidden',
            'default' => 'give_pre_form',
            LegacyFormSettingCompatibility::$key => '_give_content_placement',
        ];
    }

    /**
     * Return array configuration for form content setting field.
     *
     * Note: if you want to add an option in template to overwrite form content feature then instead of define it manually in template options, developer can call this function.
     * This function help to maintain backward compatibility with legacy donation form renderer.
     *
     * @return array
     */
    public static function getFormContentField()
    {
        return [
            'name' => __('Content', 'give'),
            'description' => __('This content will display on the single give form page.', 'give'),
            'id' => 'form_content',
            'type' => 'wysiwyg',
            'wrapper_class' => '_give_form_content_field give-hidden',
            LegacyFormSettingCompatibility::$key => '_give_form_content',
        ];
    }
}

<?php

namespace Give\FeatureFlags\OptionBasedFormEditor;

/**
 * @unreleased
 */
class OptionBasedFormEditor
{
    /**
     * @unreleased
     */
    public static function isEnabled(): bool
    {
        return true;
    }

    /**
     * @unreleased
     */
    public static function helperText(): string
    {
        return sprintf(
            '<div class="give-settings-section-group-helper">
                            <img src="%1$s" />
                            <div class="give-settings-section-group-helper__popout">
                                <img src="%2$s" />
                                <h5>%3$s</h5>
                                <p>%4$s</p>
                            </div>
                        </div>',
            esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/help-circle.svg'),
            esc_url(GIVE_PLUGIN_URL . 'assets/dist/images/admin/give-settings-gateways-v2.jpg'),
            __('Option-Based Form Editor', 'give'),
            __('This option applies only to the Option-Based Form Editor which uses the traditional settings options for creating and customizing a donation form.',
                'give')
        );
    }
}

<?php

namespace Give\Views\Admin;

use Give_License;

/**
 * Class UpsellNotices
 * @package Give\Views\Admin
 *
 * @since 2.8.0
 */
class UpsellNotice
{
    /**
     * Upsell notice for recurring addon
     */
    public static function recurringAddon()
    {
        if (Give_License::get_plugin_by_slug('give-recurring')) {
            return '';
        }

        $addon_link_url = esc_url('https://go.givewp.com/addons-recurring-inlinelink');
        $addon_button_url = esc_url('https://go.givewp.com/addons-recurring-button');

        return sprintf(
            '
			<div class="give-upsell-notice">
				<span class="icon dashicons dashicons-update-alt"></span>
				<span class="description">%1$s</span>
				<a class="view-addon-link button" href="%2$s" target="_blank">%3$s</a>
			</div>
			',
            sprintf(
                __(
                    'Activate the <a href="%1$s" title="%2$s" target="_blank">Recurring Donations add-on</a> and provide your donors with flexible subscription giving options.',
                    'give'
                ),
                $addon_link_url,
                esc_html__('Click to view the Recurring Donations add-on', 'give')
            ),
            $addon_button_url,
            esc_html__('View Add-on', 'give')
        );
    }
}

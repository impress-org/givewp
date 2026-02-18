<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Uplink;

use Give;
use Give\ServiceProviders\ServiceProvider as ServiceProviderContract;
use Give\Vendors\StellarWP\Uplink\Config;
use Give\Vendors\StellarWP\Uplink\Register;
use Give\Vendors\StellarWP\Uplink\Uplink;
use function Give\Vendors\StellarWP\Uplink\get_field;
use function Give\Vendors\StellarWP\Uplink\get_form;
use function Give\Vendors\StellarWP\Uplink\get_plugins;

class UplinkServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function register()
    {
        Config::set_container(give()->getContainer());
        Config::set_hook_prefix('givewp_');

        Uplink::init();
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Register::plugin(
            'give',
            'GiveWP',
            GIVE_VERSION,
            plugin_basename(GIVE_PLUGIN_FILE),
            Give::class
        );

        /**
         * Fix duplicate class bug: the field template puts "stellarwp-uplink-license-key-field" on both the <tr> wrapper and the inner <div> with data attributes, causing the JS to crash when it matches the <tr> (which has no data-action).
         * @see PR: https://github.com/stellarwp/uplink/pull/104
         */
        add_filter('stellarwp/uplink/' . Config::get_hook_prefix() . '/license_field_html', static function (string $html): string {
            return str_replace(
                '<tr class="stellarwp-uplink-license-key-field">',
                '<tr class="stellarwp-uplink-license-key-field-row">',
                $html
            );
        });

        add_action('admin_menu', function () {
            add_menu_page(
                'GiveWP Uplink',
                'GiveWP Uplink',
                'manage_options',
                'givewp-uplink',
                function () {
                    $form = get_form();
                    $plugins = get_plugins();

                    foreach ($plugins as $plugin) {
                        $field = get_field($plugin->get_slug());
                        $field->set_field_name('field-' . $plugin->get_slug());
                        $field->set_label($plugin->get_name());
                        $field->show_label(true);

                        $form->add_field($field);
                    }

                    $form->show_button(true, __('Submit', 'give'));
                    $form->render();
                }
            );
        }, 11);
    }
}

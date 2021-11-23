<?php

namespace Give\InPluginUpsells;

/**
 * @since 2.17.0
 */
class AddonsAdminPage
{
    protected $containerId = 'give-in-plugin-upsells';

    /**
     * Register menu item
     */
    public function register()
    {
        add_submenu_page(
            'edit.php?post_type=give_forms',
            esc_html__('GiveWP Add-ons', 'give'),
            esc_html__('Add-ons', 'give'),
            'manage_give_settings',
            'give-add-ons',
            [$this, 'render']
        );
    }

    /**
     * Load scripts
     */
    public function loadScripts()
    {
        wp_enqueue_script(
            'give-in-plugin-upsells-addons',
            GIVE_PLUGIN_URL . 'assets/dist/js/admin-upsell-addons-page.js',
            ['wp-element', 'wp-i18n', 'wp-hooks'],
            GIVE_VERSION,
            true
        );

        wp_enqueue_style(
            'give-in-plugin-upsells-addons-font',
            'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap',
            [],
            null
        );

        wp_localize_script(
            'give-in-plugin-upsells-addons',
            'GiveAddons',
            array_merge(
                (new AddonsRepository())->getAddons(),
                [
                    'assetsUrl' => GIVE_PLUGIN_URL . 'assets/dist/',
                    'containerId' => $this->containerId,
                ]
            )
        );
    }

    /**
     * Render admin page
     */
    public function render()
    {
        echo '<svg style="display: none"><path id="give-in-plugin-upsells-checkmark" d="M5.595 11.373.72 6.498a.75.75 0 0 1 0-1.06l1.06-1.061a.75.75 0 0 1 1.061 0L6.125 7.66 13.159.627a.75.75 0 0 1 1.06 0l1.061 1.06a.75.75 0 0 1 0 1.061l-8.625 8.625a.75.75 0 0 1-1.06 0Z" fill="currentColor"/></svg>';
        echo "<div id=\"{$this->containerId}\"></div>";
    }

    /**
     * Helper function to determine if current page is Give Add-ons admin page
     *
     * @return bool
     */
    public static function isShowing()
    {
        return isset($_GET['page']) && $_GET['page'] === 'give-add-ons';
    }
}

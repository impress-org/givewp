<?php

use Give\Helpers\Utils;

/**
 * Admin View: Settings
 */
if ( ! defined('ABSPATH')) {
    exit;
}

// Bailout: Do not output anything if setting tab is not defined.
if ( ! empty($tabs) && array_key_exists(give_get_current_setting_tab(), $tabs)) :
    /**
     * Filter the form action.
     *
     * Note: filter dynamically fire on basis of setting page slug
     * For example: if you register a setting page with give-settings menu slug and general current tab
     *              then filter will be give-settings_form_method_tab_general
     *
     * @since 1.8
     */
    $form_method = apply_filters(self::$setting_filter_prefix . '_form_method_tab_' . $current_tab, 'post');

    /**
     * Filter the main form tab.
     *
     * Note: You can stop print main form if you want to filter dynamically fire on basis of setting page slug
     * For example: if you register a setting page with give-settings menu slug
     *              then filter will be give-settings_open_form, give-settings_close_form
     *              We are using this filter in includes/admin/tools/class-settings-data.php#L52
     *
     * @since 1.8
     */
    $form_open_tag = apply_filters(
        self::$setting_filter_prefix . '_open_form',
        '<form method="' . $form_method . '" id="give-mainform" action="" enctype="multipart/form-data">'
    );
    $form_close_tag = apply_filters(self::$setting_filter_prefix . '_close_form', '</form>');

    $wrapper_class = implode(
        ' ',
        [
            self::$setting_filter_prefix . '-setting-page',
            self::$setting_filter_prefix . '-' . give_get_current_setting_section() . '-section',
            self::$setting_filter_prefix . '-' . give_get_current_setting_tab() . '-tab',
        ]
    );
    ?>

    <div class="wrap give-settings-page <?php
    echo esc_html($wrapper_class); ?>">

        <?php
        echo $form_open_tag; ?>

        <div class="give-settings-header">
            <?php
            /* @var Give_Settings_Page $current_setting_obj */
            if (
                ! empty($current_setting_obj)
                && method_exists($current_setting_obj, 'get_heading_html')
            ) {
                echo $current_setting_obj->get_heading_html();
            } else {
                // Backward compatibility.
                echo sprintf(
                    '<h1 class="wp-heading-inline">%s</h1>',
                    esc_html($tabs[$current_tab])
                );
            }

            self::show_messages();

            do_action('give_settings_page_header');
            ?>

        </div>

        <?php
        /*
            Default behavior of WordPress places admin notices directly after the first h-tag inside any element
            with the class of wrap. The tag below will instruct WordPress to place these notices below the header.
        */
        ?>
        <hr class="wp-header-end hidden">

        <div class="nav-tab-wrapper give-nav-tab-wrapper">
            <?php
            /**
             * Render Recurring Donations UTM link.
             *
             * @since 2.27.1
             */
            foreach ($tabs as $name => $label) {
                $target = $name === 'recurring' ? 'target="_blank" ' : false;
                $urlPath = $name === 'recurring' ? 'https://docs.givewp.com/recurring-link' : admin_url(
                    'edit.php?post_type=give_forms&page=' . self::$setting_filter_prefix . "&tab={$name}"
                );
                echo '<a ' . $target . 'href="' . $urlPath . '"' . ' class="nav-tab ' . ($current_tab === $name ? 'nav-tab-active' : 'give-mobile-hidden') . '">' . $label . '</a>';
            }

            /**
             * Render Addon product recommendation link if Recurring Donations is active.
             *
             * @since 2.27.1
             */
            if (Utils::isPluginActive('give-recurring/give-recurring.php')) {
                echo '
                        <a class="give-nav-addons-tab" href="' . esc_url(
                        admin_url('edit.php?post_type=give_forms&page=give-add-ons')
                    ) . '">
                           <img src="' . GIVE_PLUGIN_URL . '/assets/dist/images/admin/add-on-star-icon.svg"/>
                            <span>' . __('ADD-ONS', 'give') . '</span>
                        </a>
                    ';
            }

            /**
             * Trigger Action.
             *
             * Note: action dynamically fire on basis of setting page slug.
             * For example: if you register a setting page with give-settings menu slug
             *              then action will be give-settings_tabs
             *
             * @since 1.8
             */
            do_action(self::$setting_filter_prefix . '_tabs');

            // Show link to New Reports page
            $isReports = isset($_GET['page']) && $_GET['page'] === 'give-reports';
            if ($isReports === true) {
                echo sprintf(
                    '<a href="%1$s" class="nav-tab nav-tab" id="new-reports-link">%2$s</a>',
                    admin_url('edit.php?post_type=give_forms&page=give-reports'),
                    esc_html__('New Reports Dashboard', 'give')
                );
            }
            ?>

            <div class="give-sub-nav-tab-wrapper">
                <a href="#" id="give-show-sub-nav" class="nav-tab give-not-tab" title="<?php
                esc_html_e('View remaining setting tabs', 'give'); ?>">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                </a>
                <nav class="give-sub-nav-tab give-hidden"></nav>
            </div>
        </div>

        <?php

        /**
         * Trigger Action.
         *
         * Note: action dynamically fire on basis of setting page slug.
         * For example: if you register a setting page with give-settings menu slug and general current tab
         *              then action will be give-settings_sections_general_page
         *
         * @since 1.8
         */
        do_action(self::$setting_filter_prefix . "_sections_{$current_tab}_page");

        /**
         * Trigger Action.
         *
         * Note: action dynamically fire on basis of setting page slug.
         * For example: if you register a setting page with give-settings menu slug and general current tab
         *              then action will be give-settings_settings_general_page
         *
         * @since 1.8
         */
        do_action(self::$setting_filter_prefix . "_settings_{$current_tab}_page");

        wp_nonce_field('give-save-settings', '_give-save-settings');

        if (empty($GLOBALS['give_hide_save_button'])) :
            ?>
            <div class="give-submit-wrap">
                <input name="save" class="button-primary give-save-button" type="submit" value="<?php
                esc_html_e('Save changes', 'give'); ?>" />
            </div>
        <?php
        endif; ?>
        <?php
        echo $form_close_tag; ?>
    </div>
<?php
else :
    ?>
    <div class="error">
        <p>
            <?php
            esc_html_e('Oops, this settings page does not exist.', 'give'); ?>
        </p>
    </div>
<?php
endif;
?>

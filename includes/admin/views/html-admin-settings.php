<?php

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
    $form_open_tag = apply_filters(self::$setting_filter_prefix . '_open_form',
        '<form method="' . $form_method . '" id="give-mainform" action="" enctype="multipart/form-data">');
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
            foreach ($tabs as $name => $label) {
                echo '<a href="' . admin_url('edit.php?post_type=give_forms&page=' . self::$setting_filter_prefix . "&tab={$name}") . '" class="nav-tab ' . ($current_tab === $name ? 'nav-tab-active' : 'give-mobile-hidden') . '">' . $label . '</a>';
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

            <?php
            if (class_exists('Give_Recurring')) {
                echo '
                    <a class="give-nav-addons-tab" href="/wp-admin/edit.php?post_type=give_forms&page=give-add-ons" target="blank">
                        <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M4.5 1C4.5 0.447715 4.05228 0 3.5 0C2.94772 0 2.5 0.447715 2.5 1V2.5H1C0.447715 2.5 0 2.94772 0 3.5C0 4.05228 0.447715 4.5 1 4.5H2.5V6C2.5 6.55228 2.94772 7 3.5 7C4.05228 7 4.5 6.55228 4.5 6V4.5H6C6.55228 4.5 7 4.05228 7 3.5C7 2.94772 6.55228 2.5 6 2.5H4.5V1Z"
                                fill="#0C7FF2" />
                            <path
                                d="M4.5 16C4.5 15.4477 4.05228 15 3.5 15C2.94772 15 2.5 15.4477 2.5 16V17.5H1C0.447715 17.5 0 17.9477 0 18.5C0 19.0523 0.447715 19.5 1 19.5H2.5V21C2.5 21.5523 2.94772 22 3.5 22C4.05228 22 4.5 21.5523 4.5 21V19.5H6C6.55228 19.5 7 19.0523 7 18.5C7 17.9477 6.55228 17.5 6 17.5H4.5V16Z"
                                fill="#0C7FF2" />
                            <path
                                d="M12.9333 1.64102C12.7848 1.25483 12.4138 1 12 1C11.5862 1 11.2152 1.25483 11.0667 1.64102L9.33248 6.14988C9.03207 6.93093 8.93768 7.156 8.80855 7.33759C8.67899 7.5198 8.5198 7.67899 8.33759 7.80855C8.156 7.93768 7.93093 8.03207 7.14988 8.33248L2.64102 10.0667C2.25483 10.2152 2 10.5862 2 11C2 11.4138 2.25483 11.7848 2.64102 11.9333L7.14988 13.6675C7.93093 13.9679 8.156 14.0623 8.33759 14.1914C8.5198 14.321 8.67899 14.4802 8.80855 14.6624C8.93768 14.844 9.03207 15.0691 9.33248 15.8501L11.0667 20.359C11.2152 20.7452 11.5862 21 12 21C12.4138 21 12.7848 20.7452 12.9333 20.359L14.6675 15.8501C14.9679 15.0691 15.0623 14.844 15.1914 14.6624C15.321 14.4802 15.4802 14.321 15.6624 14.1914C15.844 14.0623 16.0691 13.9679 16.8501 13.6675L21.359 11.9333C21.7452 11.7848 22 11.4138 22 11C22 10.5862 21.7452 10.2152 21.359 10.0667L16.8501 8.33248C16.0691 8.03207 15.844 7.93768 15.6624 7.80855C15.4802 7.67899 15.321 7.5198 15.1914 7.33759C15.0623 7.156 14.9679 6.93093 14.6675 6.14988L12.9333 1.64102Z"
                                fill="#0C7FF2" />
                        </svg>

                        <span>
                            ADD-ONS
                        </span>
                    </a>
               ';
            }
            ?>

            <div class="give-sub-nav-tab-wrapper">
                <a href="#" id="give-show-sub-nav" class="nav-tab give-not-tab"
                   title="<?php
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
			<?php esc_html_e( 'Oops, this settings page does not exist.', 'give' ); ?>
		</p>
	</div>
	<?php
endif;
?>

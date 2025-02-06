<?php

namespace Give\Helpers;

use SitePress;
use WPML_Request;

/**
 * @since 3.0.0
 */
class Language
{
    /**
     * @since 3.0.0
     */
    public static function load()
    {
        $giveRelativePath = self::getRelativePath();

        $locale = is_admin() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
        $locale = apply_filters('plugin_locale', $locale, 'give'); // Traditional WordPress plugin locale filter.

        // Setup paths to current locale file.
        $moFile = sprintf('%1$s-%2$s.mo', 'give', $locale);
        $moFileLocal = trailingslashit(WP_PLUGIN_DIR) . $giveRelativePath . $moFile;
        $moFileGlobal = trailingslashit(WP_LANG_DIR) . 'plugins/' . $moFile;

        unload_textdomain('give');
        if (file_exists($moFileGlobal)) {
            load_textdomain('give', $moFileGlobal); // Look in global /wp-content/languages/plugins folder.
        } elseif (file_exists($moFileLocal)) {
            load_textdomain('give', $moFileLocal); // Look in local /wp-content/plugins/give/languages/ folder.
        } else {
            load_plugin_textdomain('give', false, $giveRelativePath); // Load the default language files.
        }
    }

    /**
     * @since 3.0.0
     */
    public static function setScriptTranslations($handle)
    {
        wp_set_script_translations($handle, 'give', trailingslashit(WP_PLUGIN_DIR) . self::getRelativePath());
    }

    /**
     * Return the plugin language dir relative path, e.g. "give/languages/"
     *
     * @since 3.0.0
     */
    public static function getRelativePath(): string
    {
        $giveRelativePath = dirname(plugin_basename(GIVE_PLUGIN_FILE)) . '/languages/';
        $giveRelativePath = ltrim(apply_filters('give_languages_directory', $giveRelativePath), '/\\');

        return trailingslashit($giveRelativePath);
    }

    /**
     * @unreleased
     */
    public static function getLocale()
    {
        $locale = get_locale();

        /**
         * @var WPML_Request $wpml_request_handler
         * @var SitePress    $sitepress
         */
        global $wpml_request_handler, $sitepress;

        // When in the admin area and WPML is installed, retrieve the language selected in the WPML language selector of the WordPress admin bar
        if (isset($wpml_request_handler) && is_admin()) {
            $requestedLang = $wpml_request_handler->get_requested_lang();
            $wpmlLocale = $sitepress->get_locale($requestedLang);
            $locale = $wpmlLocale != $locale ? $wpmlLocale : $locale;
        }

        return $locale;
    }

    /**
     * @unreleased
     */
    public static function switchToLocale(string $locale)
    {
        if (empty($locale) || $locale == get_locale()) {
            return;
        }

        switch_to_locale($locale);
    }
}

<?php

namespace Give\Helpers;

use WP_Translation_Controller;
use WP_Translations;

/**
 * @since 3.0.0
 */
class Language
{
    /**
     * @unreleased Added early return if the textdomain is loaded from a custom folder
     * @since 3.0.0
     */
    public static function load()
    {
        if (self::isTextdomainLoadedFromCustomFolder('give')) {
            return;
        }

        $giveRelativePath = self::getRelativePath();

        $locale = is_admin() && ! wp_doing_ajax() && function_exists('get_user_locale') ? get_user_locale() : get_locale();
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
     * @since 3.22.0
     */
    public static function getLocale()
    {
        return apply_filters('givewp_locale', get_locale());
    }

    /**
     * @since 3.22.0
     */
    public static function switchToLocale(string $locale)
    {
        if (empty($locale) || $locale == get_locale()) {
            return;
        }

        switch_to_locale($locale);
    }

    /**
     * Check if a textdomain is loaded from a custom folder (not in standard WordPress directories)
     * 
     * @unreleased
     * @param string $textdomain The textdomain to check
     * @return bool True if loaded from custom folder, false otherwise
     */
    public static function isTextdomainLoadedFromCustomFolder(string $textdomain): bool
    {
        global $l10n;
        if (!isset($l10n[$textdomain]) || !$l10n[$textdomain] instanceof WP_Translations) {
            return false;
        }

        // Try to access the controller through the global instance
        $controller = WP_Translation_Controller::get_instance();
        if (!$controller) {
            return false;
        }

        // Use reflection to access the protected loaded_translations property
        $reflection = new \ReflectionClass($controller);
        $loadedTranslationsProperty = $reflection->getProperty('loaded_translations');
        $loadedTranslationsProperty->setAccessible(true);
        $loadedTranslations = $loadedTranslationsProperty->getValue($controller);
        
        $currentLocale = $controller->get_locale();
        if (!isset($loadedTranslations[$currentLocale][$textdomain])) {
            return false;
        }

        $files = $loadedTranslations[$currentLocale][$textdomain];
        foreach ($files as $file) {
            if (method_exists($file, 'get_file')) {
                $loadedFile = $file->get_file();
                
                // Check if the file is in a custom location
                $isNotInPluginDir = strpos($loadedFile, WP_PLUGIN_DIR) === false;
                
                // Check if it's in WP_LANG_DIR but in a custom subdirectory
                $isInCustomLocation = false;
                if (strpos($loadedFile, WP_LANG_DIR) === 0) {
                    // It's in WP_LANG_DIR, check if it's in a custom subdirectory
                    $relativePath = substr($loadedFile, strlen(WP_LANG_DIR));
                    // Standard subdirectories are /plugins/ and /themes/
                    // Custom subdirectories would be anything else like /loco/, /custom/, etc.
                    $isInCustomLocation = strpos($relativePath, '/plugins/') !== 0 && 
                                         strpos($relativePath, '/themes/') !== 0 &&
                                         strpos($relativePath, '/') !== false;
                } else {
                    // Not in WP_LANG_DIR at all, so it's custom
                    $isInCustomLocation = true;
                }
                
                if ($isNotInPluginDir && $isInCustomLocation) {
                    // Custom translation file found
                    return true;
                }
            }
        }

        return false;
    }
}

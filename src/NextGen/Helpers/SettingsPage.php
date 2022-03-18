<?php

namespace Give\NextGen\Helpers;

use Give_Admin_Settings;
use Give_Settings_Page;
use InvalidArgumentException;

/**
 * Helper class responsible for adding settings pages.
 *
 * @package     Give\Addon\Helpers
 * @copyright   Copyright (c) 2020, GiveWP
 */
class SettingsPage
{

    /**
     * Register settings page.
     *
     * @since 1.0.0
     *
     * @param string $class subclass of Give_Settings_Page
     *
     * @return void
     */
    public static function registerPage($class)
    {
        add_filter(
            'give-settings_get_settings_pages',
            function () use ($class) {
                if ( ! class_exists($class)) {
                    throw new InvalidArgumentException("The class {$class} does not exist");
                }

                if ( ! is_subclass_of($class, Give_Settings_Page::class)) {
                    throw new InvalidArgumentException(
                        "{$class} class must extend the Give_Settings_Page class"
                    );
                }

                return give($class)->get_settings();
            }
        );
    }

    /**
     * Add settings to the existing Settings page.
     *
     * @since 1.0.0
     *
     * @param string $sectionId - settings page section
     * @param array  $settings
     *
     * @param string $settingsId - settings page ID
     *
     * @return void
     */
    public static function addSettings($settingsId, $sectionId, $settings)
    {
        add_filter(
            sprintf('give_get_settings_%s', $settingsId),
            function ($pageSettings) use ($settingsId, $sectionId, $settings) {
                // Check settings page and section
                if ( ! Give_Admin_Settings::is_setting_page($settingsId, $sectionId)) {
                    return $pageSettings;
                }

                return array_merge($pageSettings, $settings);
            }
        );
    }

    /**
     * Add Settings page section.
     *
     * @since 1.0.0
     *
     * @param string $sectionId
     * @param string $sectionName
     *
     * @param string $settingsId - settings page ID
     *
     * @return void
     */
    public static function addPageSection($settingsId, $sectionId, $sectionName)
    {
        add_filter(
            sprintf('give_get_sections_%s', $settingsId),
            function ($sections) use ($sectionId, $sectionName) {
                $sections[$sectionId] = $sectionName;

                return $sections;
            }
        );
    }

    /**
     * Remove Settings page section.
     *
     * @since 1.0.0
     *
     * @param string $sectionId
     *
     * @param string $settingsId - settings page ID
     *
     * @return void
     */
    public static function removePageSection($settingsId, $sectionId)
    {
        add_filter(
            sprintf('give_get_sections_%s', $settingsId),
            function ($sections) use ($sectionId) {
                if (isset($sections[$sectionId])) {
                    unset($sections[$sectionId]);
                }

                return $sections;
            },
            999
        );
    }

}

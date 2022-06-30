<?php

namespace Give\Helpers;

/**
 * Class Utils
 *
 * @package Give\Helpers
 */
class Utils
{
    /**
     * Extract query param from URL
     *
     * @since 2.7.0
     *
     * @param string $url
     * @param string $queryParamName
     * @param mixed  $default
     *
     * @return string
     */
    public static function getQueryParamFromURL($url, $queryParamName, $default = '')
    {
        $queryArgs = wp_parse_args(parse_url($url, PHP_URL_QUERY));

        return isset($queryArgs[$queryParamName]) ? give_clean($queryArgs[$queryParamName]) : $default;
    }

    /**
     * This function will change request url with other url.
     *
     * @since 2.7.0
     *
     * @param string $location Requested URL.
     * @param string $url URL.
     * @param array  $removeArgs Remove extra query params.
     * @param array  $addArgs add extra query params.
     *
     * @return string
     */
    public static function switchRequestedURL($location, $url, $addArgs = [], $removeArgs = [])
    {
        $queryString = [];

        if ($index = strpos($location, '?')) {
            $queryString = wp_parse_args(substr($location, strpos($location, '?') + 1));
        }

        if ($index = strpos($url, '?')) {
            $queryString = array_merge($queryString, wp_parse_args(substr($url, strpos($url, '?') + 1)));
        }

        $url = add_query_arg(
            $queryString,
            $url
        );

        if ($removeArgs) {
            foreach ($removeArgs as $name) {
                $url = add_query_arg([$name => false], $url);
            }
        }

        if ($addArgs) {
            foreach ($addArgs as $name => $value) {
                $url = add_query_arg([$name => $value], $url);
            }
        }

        return esc_url_raw($url);
    }

    /**
     * Remove giveDonationAction  from URL.
     *
     * @since 2.7.0
     *
     * @param $url
     *
     * @return string
     */
    public static function removeDonationAction($url)
    {
        return esc_url_raw( add_query_arg(['giveDonationAction' => false], $url) );
    }

    /**
     * Determines whether a plugin is active.
     *
     * Only plugins installed in the plugins/ folder can be active.
     *
     * Plugins in the mu-plugins/ folder can't be "activated," so this function will
     * return false for those plugins.
     *
     * For more information on this and similar theme functions, check out
     * the {@link https://developer.wordpress.org/themes/basics/conditional-tags/
     * Conditional Tags} article in the Theme Developer Handbook.
     *
     * @since 2.7.0
     *
     * @param string $plugin Path to the plugin file relative to the plugins directory.
     *
     * @return bool True, if in the active plugins list. False, not in the list.
     */
    public static function isPluginActive($plugin)
    {
        if ( ! function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return is_plugin_active($plugin);
    }
}

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

    /**
     * @since 3.17.2
     */
    public static function removeBackslashes($data)
    {
        /**
         * The stripslashes_deep() method removes only the first backslash occurrence from
         * a given string, so we are using the ltrim() method to make sure we are removing
         * all other occurrences. We need to remove these backslashes from the beginner of
         * the input because attackers can use them to bypass the is_serialized() check.
         */
        $data = stripslashes_deep($data);
        $data = is_string($data) ? ltrim($data, '\\') : $data;

        return $data;
    }

    /**
     * Decode strings recursively to prevent double (or more) encoded strings
     *
     * @since 3.19.4
     */
    public static function recursiveUrlDecode(string $data): string
    {
        $decoded = urldecode($data);

        return $decoded === $data ? $data : self::recursiveUrlDecode($decoded);
    }

    /**
     * The regular expression attempts to capture the basic structure of all data types that can be serialized by PHP.
     *
     * @since 3.19.4 Decode the string and remove any character not allowed in a serialized string
     * @since 3.19.3 Support all types of serialized data instead of only objects and arrays
     * @since 3.17.2
     */
    public static function containsSerializedDataRegex($data): bool
    {
        if ( ! is_string($data)) {
            return false;
        }

        $data = self::recursiveUrlDecode($data);

        /**
         * This regular expression removes any special character that is not:
         * a Letter (a-zA-Z), number (0-9), or any of the characters {}, :, ;, ", ', ., [, ], (, ), ,
         */
        $data = preg_replace('/[^a-zA-Z0-9:{};"\'.\[\](),]/', '', $data);

        $pattern = '/
        (a:\d+:\{.*}) |         # Matches arrays (e.g: a:2:{i:0;s:5:"hello";i:1;i:42;})
        (O:\d+:"[^"]+":\{.*}) | # Matches objects (e.g: O:8:"stdClass":1:{s:4:"name";s:5:"James";})
        (s:\d+:"[^"]*";) |       # Matches strings (e.g: s:5:"hello";)
        (i:\d+;) |               # Matches integers (e.g: i:42;)
        (b:[01];) |              # Matches booleans (e.g: b:1; or b:0;)
        (d:\d+(\.\d+)?;) |       # Matches floats (e.g: d:3.14;)
        (N;)                     # Matches NULL (e.g: N;)
        /x';

        return preg_match($pattern, $data) === 1;
    }

    /**
     * @since 3.17.2
     */
    public static function isSerialized($data): bool
    {
        $data = self::removeBackslashes($data);

        if (is_serialized($data) || self::containsSerializedDataRegex($data)) {
            return true;
        }

        return false;
    }

    /**
     * @since 3.17.2
     */
    public static function safeUnserialize($data)
    {
        $data = self::removeBackslashes($data);

        /**
         * We are setting the allowed_classes to false as a default to
         * prevent the injection of objects that can run unwished code.
         *
         * From PHP docs:
         * allowed_classes - Either an array of class names which should be accepted, false to accept no classes, or
         * true to accept all classes. If this option is defined and unserialize() encounters an object of a class
         * that isn't to be accepted, then the object will be instantiated as __PHP_Incomplete_Class instead. Omitting
         * this option is the same as defining it as true: PHP will attempt to instantiate objects of any class.
         */
        $unserializedData = @unserialize(trim($data), ['allowed_classes' => false]);

        /*
         * In case the passed string is not unserializeable, false is returned.
         *
         * @see https://www.php.net/manual/en/function.unserialize.php
         */

        return ! $unserializedData && ! self::containsSerializedDataRegex($data) ? $data : $unserializedData;
    }

    /**
     * Avoid insecure usage of `unserialize` when the data could be submitted by the user.
     *
     * @since 3.16.1
     *
     * @param string $data Data that might be unserialized.
     *
     * @return mixed Unserialized data can be any type.
     */
    public static function maybeSafeUnserialize($data)
    {
        return self::isSerialized($data)
            ? self::safeUnserialize($data)
            : $data;
    }
}

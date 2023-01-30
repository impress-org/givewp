<?php

namespace Give\Addon;

use InvalidArgumentException;

/**
 * Helper class responsible for loading add-on views.
 *
 * @package     Give\Addon\Helpers
 * @copyright   Copyright (c) 2020, GiveWP
 */
class View
{
    /**
     * Default add-on domain
     */
    const DEFAULT_DOMAIN = 'NextGen';

    /**
     * @since 0.1.0
     *
     * @param  array  $templateParams  Arguments for template.
     * @param  bool  $echo
     *
     * @param  string  $view  Template name
     * When using multiple domains within this add-on, the domain directory can be set by using "." in the template name.
     * String before the "." character is domain directory, and everything after is the template file path
     * Example usage: View::render( 'DomainName.templateName' );
     * This will try to load src/NextGenName/resources/view/templateName.php file
     *
     * @return string|void
     * @throws InvalidArgumentException if template file not exist
     *
     */
    public static function load($view, $templateParams = [], $echo = false)
    {
        // Get domain and file path
        list ($domain, $file) = static::getPaths($view);
        $template = GIVE_NEXT_GEN_DIR . "src/{$domain}/resources/views/{$file}.php";

        if ( ! file_exists($template)) {
            throw new InvalidArgumentException("View template file {$template} does not exist");
        }

        ob_start();
        extract($templateParams);
        include $template;
        $content = ob_get_clean();

        if ( ! $echo) {
            return $content;
        }

        echo $content;
    }

    /**
     * @since 0.1.0
     *
     * @param  array  $vars
     *
     * @param  string  $view
     */
    public static function render($view, $vars = [])
    {
        static::load($view, $vars, true);
    }

    /**
     * Get domain and template file path
     *
     * @since 0.1.0
     *
     * @param  string  $path
     *
     * @return array
     */
    private static function getPaths($path)
    {
        // Check for . delimiter
        if (false === strpos($path, '.')) {
            return [
                self::DEFAULT_DOMAIN,
                $path,
            ];
        }

        return explode('.', $path, 2);
    }
}

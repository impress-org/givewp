<?php

namespace Give\DonationForms\Routes;

use WP;

/**
 * Serves the external embed script from a URL the plugin controls, so the
 * snippet copied onto third-party sites keeps working if the build path moves.
 *
 * Pretty permalinks: /give/embed/donation-form/script.js
 * Plain permalinks:  /?givewp-embed-script=donation-form
 *
 * @since TBD
 */
class ExternalEmbedScriptRoute
{
    public const PATH = 'give/embed/donation-form/script.js';
    public const QUERY_VAR = 'givewp-embed-script';
    public const QUERY_VALUE = 'donation-form';
    public const SCRIPT_FILE = 'build/externalFormEmbed.js';

    /**
     * @since TBD
     */
    public static function url(): string
    {
        return get_option('permalink_structure')
            ? home_url('/' . self::PATH)
            : add_query_arg(self::QUERY_VAR, self::QUERY_VALUE, home_url('/'));
    }

    /**
     * @since TBD
     */
    public function __invoke(WP $wp): void
    {
        if (!$this->isRequested($wp)) {
            return;
        }

        $this->serve(GIVE_PLUGIN_DIR . self::SCRIPT_FILE);
    }

    /**
     * @since TBD
     */
    public function isRequested(WP $wp): bool
    {
        return $wp->request === self::PATH
            || (isset($_GET[self::QUERY_VAR]) && $_GET[self::QUERY_VAR] === self::QUERY_VALUE);
    }

    /**
     * @since TBD
     */
    protected function serve(string $file): void
    {
        if (!is_readable($file)) {
            status_header(404);
            exit;
        }

        $etag = '"' . md5_file($file) . '"';

        header('Content-Type: application/javascript; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=3600');
        header('ETag: ' . $etag);

        if ($this->matchesIfNoneMatch($etag)) {
            status_header(304);
            exit;
        }

        readfile($file);
        exit;
    }

    /**
     * @since TBD
     */
    protected function matchesIfNoneMatch(string $etag): bool
    {
        if (empty($_SERVER['HTTP_IF_NONE_MATCH'])) {
            return false;
        }

        $candidates = array_map(static function (string $value): string {
            return trim(str_replace('W/', '', $value));
        }, explode(',', wp_unslash($_SERVER['HTTP_IF_NONE_MATCH'])));

        return in_array($etag, $candidates, true) || in_array('*', $candidates, true);
    }
}

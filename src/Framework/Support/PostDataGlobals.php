<?php

namespace Give\Framework\Support;

/**
 * Captures and restores the global variables WordPress core's setup_postdata() writes to.
 *
 * wp_reset_postdata() repopulates them from $wp_query->post, so it only undoes a secondary loop
 * when the main query actually matched a post. On an admin screen it never does, and the last
 * iterated post's data stays in scope for the rest of the request. WordPress itself reads some of
 * it back — global $id in particular, which network_edit_site_nav() uses to build Multisite site
 * links — so a secondary loop has to put these back itself.
 *
 * @since TBD
 */
final class PostDataGlobals
{
    /**
     * @var string[] Names of the globals setup_postdata() declares and assigns.
     */
    private static $globalNames = [
        'id',
        'authordata',
        'currentday',
        'currentmonth',
        'page',
        'pages',
        'multipage',
        'more',
        'numpages',
    ];

    /**
     * Records the current value of each global setup_postdata() writes to.
     *
     * Globals that are not set are omitted rather than recorded as null, so that restore() can
     * tell "was absent" apart from "was null" and avoid declaring a global that never existed.
     *
     * @since TBD
     *
     * @return array{id?: mixed, authordata?: mixed, currentday?: mixed, currentmonth?: mixed, page?: mixed,
     *               pages?: mixed, multipage?: mixed, more?: mixed, numpages?: mixed} Values keyed by global
     *               name, containing only the globals that were set.
     */
    public static function snapshot(): array
    {
        $snapshot = [];

        foreach (self::$globalNames as $globalName) {
            if (array_key_exists($globalName, $GLOBALS)) {
                $snapshot[$globalName] = $GLOBALS[$globalName];
            }
        }

        return $snapshot;
    }

    /**
     * Returns the globals setup_postdata() writes to back to their recorded values.
     *
     * @since TBD
     *
     * @param array{id?: mixed, authordata?: mixed, currentday?: mixed, currentmonth?: mixed, page?: mixed,
     *              pages?: mixed, multipage?: mixed, more?: mixed, numpages?: mixed} $snapshot Values keyed
     *              by global name, as returned by snapshot().
     *
     * @return void
     */
    public static function restore(array $snapshot)
    {
        foreach (self::$globalNames as $globalName) {
            if (array_key_exists($globalName, $snapshot)) {
                $GLOBALS[$globalName] = $snapshot[$globalName];
            } else {
                unset($GLOBALS[$globalName]);
            }
        }
    }
}

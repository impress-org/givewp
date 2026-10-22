<?php

namespace Give\Uninstall\Actions;

use Give_Cache;

/**
 * Deletes the options that GiveWP and its add-ons created, when the data is removed on uninstall.
 *
 * An option belongs to GiveWP when its name starts with one of the GiveWP prefixes. The old uninstall
 * code matched any option with "give" anywhere in its name, so it also deleted options of other
 * plugins (for example "givebutter-widget-account").
 *
 * @since TBD
 */
class DeleteGiveOptions
{
    /**
     * @since TBD
     *
     * @return string[] The names of the deleted options.
     */
    public function __invoke(): array
    {
        $optionNames = $this->getOptionNames();

        foreach ($optionNames as $optionName) {
            if (false !== strpos($optionName, 'give_cache')) {
                Give_Cache::delete($optionName);
            } else {
                delete_option($optionName);
            }
        }

        return $optionNames;
    }

    /**
     * @since TBD
     *
     * @return string[]
     */
    public function getOptionNames(): array
    {
        global $wpdb;

        $patterns = $this->getLikePatterns();

        $names = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT option_name FROM {$wpdb->options} WHERE " . implode(' OR ', array_fill(0, count($patterns), 'option_name LIKE %s')),
                $patterns
            )
        );

        return is_array($names) ? $names : [];
    }

    /**
     * The name prefixes of the options GiveWP and its add-ons create, as LIKE patterns:
     * `give_*` (includes the cache options), `givewp_*`, widget options, transients, and the
     * database version options of the GiveWP tables (`{prefix}give_*_db_version`).
     *
     * @since TBD
     *
     * @return string[]
     */
    public function getLikePatterns(): array
    {
        global $wpdb;

        $prefixes = [
            'give_',
            'givewp_',
            '_give_',
            'widget_give_',
            '_transient_give_',
            '_transient_timeout_give_',
            '_site_transient_give_',
            '_site_transient_timeout_give_',
            $wpdb->prefix . 'give_',
        ];

        return array_map(
            static function (string $prefix) use ($wpdb): string {
                return $wpdb->esc_like($prefix) . '%';
            },
            array_values(array_unique($prefixes))
        );
    }
}

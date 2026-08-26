<?php

declare(strict_types=1);

namespace Give\VendorOverrides\Harbor\Actions;

/**
 * Whether Harbor has fully loaded, which only happens when a premium plugin
 * is installed and active.
 *
 * Harbor is vendor-prefixed per host plugin, so only one Harbor copy runs as
 * the version leader so we cannot use the classes to determine if Harbor is loaded.
 */
class HarborHasLoaded
{
    /**
     * @since 4.16.7
     *
     * Safe to call on or after `plugins_loaded`. Harbor fires `lw_harbor/loaded`
     * during its init (Give boots Harbor from `plugins_loaded`), and only when
     * at least one premium plugin reports itself via `lw_harbor/premium_plugin_exists`.
     *
     * @return bool Whether Harbor has fully loaded.
     */
    public function __invoke(): bool
    {
        return (bool) did_action('lw_harbor/loaded');
    }
}

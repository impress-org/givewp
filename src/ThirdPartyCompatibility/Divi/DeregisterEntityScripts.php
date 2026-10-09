<?php

namespace Give\ThirdPartyCompatibility\Divi;

/**
 * @since 4.13.0 include divi options page
 * @since 4.5.0
 */
class DeregisterEntityScripts
{
    public function __invoke()
    {
        if ( ! isset($_GET['page']) || ! in_array($_GET['page'], ['et_theme_builder', 'et_divi_options'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page check; it only decides whether the entity scripts are deregistered.
            return;
        }

        global $wp_scripts;

        $registered_scripts = $wp_scripts->registered;

        foreach ($registered_scripts as $handle => $script) {
            if (preg_match('/^givewp-.*-entity$/', $handle)) {
                wp_dequeue_script($handle);
                wp_deregister_script($handle);
            }
        }
    }
}

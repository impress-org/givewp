<?php

namespace Give\ThirdPartyCompatibility\Divi;

/**
 * @since 4.5.0
 */
class DeregisterEntityScripts
{
    public function __invoke()
    {
        if ( ! isset($_GET['page']) || $_GET['page'] !== 'et_theme_builder') {
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

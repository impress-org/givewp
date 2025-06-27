<?php

namespace Give\ThirdPartyCompatibility\Divi;

/**
 * @unreleased
 */
class DeregisterEntityScripts
{
    public function __invoke()
    {
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

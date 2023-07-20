<?php

namespace Give\Framework\DesignSystem\Actions;

class RegisterDesignSystemStyles
{
    /**
     * @since 2.26.0
     */
    public function __invoke()
    {
        $version = file_get_contents(GIVE_PLUGIN_DIR . 'assets/dist/css/design-system/version');

        wp_register_style(
            'givewp-design-system-foundation',
            GIVE_PLUGIN_URL . 'assets/dist/css/design-system/foundation.css',
            [],
            $version
        );
    }
}

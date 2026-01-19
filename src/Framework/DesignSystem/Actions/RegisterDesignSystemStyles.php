<?php

namespace Give\Framework\DesignSystem\Actions;

use Give\Helpers\Frontend\Page;

class RegisterDesignSystemStyles
{
    /**
     * @unreleased Prevent loading styles on non-Give pages.
     * @since 2.26.0
     */
    public function __invoke()
    {
        if (!is_admin() && !Page::hasGiveContent()) {
            return;
        }

        $version = file_get_contents(GIVE_PLUGIN_DIR . 'build/assets/dist/css/design-system/version');

        wp_register_style(
            'givewp-design-system-foundation',
            GIVE_PLUGIN_URL . 'build/assets/dist/css/design-system/foundation.css',
            [],
            $version
        );
    }
}

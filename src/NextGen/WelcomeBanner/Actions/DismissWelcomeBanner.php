<?php

namespace Give\NextGen\WelcomeBanner\Actions;

class DismissWelcomeBanner
{
    public function __invoke() {
        check_ajax_referer( 'givewp_next_gen_welcome_banner_dismiss', 'nonce' );
        update_option('givewp_next_gen_welcome_banner_dismissed', time());
        wp_die();
    }
}

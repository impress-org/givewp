<?php

namespace Give\Settings\Security;

use Give_Settings_Page;

/**
 * @since 3.17.0
 */
class SecuritySettingsPage extends Give_Settings_Page {

    /**
     * @since 3.17.0
     */
    public function __construct() {
        $this->id    = 'security';
        $this->label = __( 'Security', 'give' );
        $this->default_tab = 'security';

        parent::__construct();
    }
}

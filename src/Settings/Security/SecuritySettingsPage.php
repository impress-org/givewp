<?php

namespace Give\Settings\Security;

use Give_Settings_Page;

/**
 * @unreleased
 */
class SecuritySettingsPage extends Give_Settings_Page {

    /**
     * @unreleased
     */
    public function __construct() {
        $this->id    = 'security';
        $this->label = __( 'Security', 'give' );
        $this->default_tab = 'security';

        parent::__construct();
    }
}

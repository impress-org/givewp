<?php

namespace Give\Addon;

use Give_License;

class License
{

    /**
     * Check add-on license.
     *
     * @since 0.1.0
     * @return void
     */
    public function check()
    {
        new Give_License(
            GIVE_NEXT_GEN_FILE,
            GIVE_NEXT_GEN_NAME,
            GIVE_NEXT_GEN_VERSION,
            'GiveWP'
        );
    }
}

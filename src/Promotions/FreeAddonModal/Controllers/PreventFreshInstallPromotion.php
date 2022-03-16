<?php

namespace Give\Promotions\FreeAddonModal\Controllers;

class PreventFreshInstallPromotion
{
    /**
     * Set the option to prevent
     *
     * @return void
     */
    public function __invoke()
    {
        $upgrade = get_option('give_version_upgraded_from');

        if ( ! $upgrade && ! get_option('give_free_addon_modal_displayed') ) {
            update_option('give_free_addon_modal_displayed', 'prevent:1:' . GIVE_VERSION);
        }
    }
}

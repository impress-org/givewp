<?php

namespace Give\Promotions\FreeAddonModal\Controllers;

class DisplaySettingsButton
{
    use CheckOfferStatus;

    /**
     * @return void
     */
    public function __invoke()
    {
        if ( !$this->displayOffer() ) {
            return;
        }

        $url = admin_url( 'edit.php?post_type=give_forms&page=give-add-ons&tab=3' );
        echo "<a class='give-green-button i-crown' href='$url' target='_blank'>Get a Free Add-On</a>";
    }
}

<?php

namespace Give\Promotions\FreeAddonModal;

class Controller
{
    public function enqueueScripts()
    {
        if ( !$this->displayModal() ) {
            return;
        }

        wp_enqueue_script('give_free_addon_modal', GIVE_PLUGIN_URL . 'assets/dist/js/admin-free-addon-modal.js', [], GIVE_VERSION, true);
    }

    /**
     * Whether the modal should be displayed.
     *
     * @return bool
     */
    private function displayModal()
    {
        // Only display the modal if the user is an admin
        if ( !current_user_can('manage_options')) {
            return false;
        }

        // Only display if the user did not dismiss or subscribe
        if ( get_option('give_free_addon_modal_displayed') ) {
            return false;
        }

        return true;
    }
}

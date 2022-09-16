<?php

namespace Give\FormBuilder\Routes;

/**
 * Route to edit an existing form
 */
class EditFormRoute
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function __invoke()
    {
        if (isset($_GET['post'], $_GET['action']) && 'edit' === $_GET['action']) {
            $post = get_post(abs($_GET['post']));
            if ('give_forms' === $post->post_type && $post->post_content) {
                wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=' . $post->ID);
                exit();
            }
        }
    }
}

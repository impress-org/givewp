<?php

namespace Give\FormBuilder\Routes;

use Give\FormBuilder\FormBuilderRouteBuilder;

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
                wp_redirect(FormBuilderRouteBuilder::makeEditFormRoute($post->ID));
                exit();
            }
        }
    }
}

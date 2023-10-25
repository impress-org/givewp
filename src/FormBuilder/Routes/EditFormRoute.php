<?php

namespace Give\FormBuilder\Routes;

use Give\FormBuilder\FormBuilderRouteBuilder;
use Give\Helpers\Form\Utils;

/**
 * Route to edit an existing form
 */
class EditFormRoute
{
    /**
     * @since 3.0.3 Use isV3Form() method instead of 'post_content' to check if the form is built with Visual Builder
     * @since 3.0.0
     *
     * @return void
     */
    public function __invoke()
    {
        if (isset($_GET['post'], $_GET['action']) && 'edit' === $_GET['action']) {
            $post = get_post(abs($_GET['post']));
            if ('give_forms' === $post->post_type && Utils::isV3Form($post->ID)) {
                wp_redirect(FormBuilderRouteBuilder::makeEditFormRoute($post->ID));
                exit();
            }
        }
    }
}

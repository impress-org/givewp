<?php

namespace Give\FormBuilder\Routes;

/**
 * Route to create a new form
 */
class CreateFormRoute
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function __invoke()
    {
        if (isset($_GET['page']) && 'campaign-builder' === $_GET['page']) {
            // Little hack for alpha users to make sure the form builder is loaded.
            if (!isset($_GET['donationFormID'])) {
                wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=new');
                exit();
            }
            if ('new' === $_GET['donationFormID']) {
                $newPostID = wp_insert_post([
                    'post_type' => 'give_forms',
                    'post_status' => 'publish',
                    'post_content' => json_encode(null),
                ]);

                wp_update_post([
                    'ID' => $newPostID,
                    'post_title' => "Next Gen Donation Form ID:$newPostID",
                ]);

                wp_redirect('edit.php?post_type=give_forms&page=campaign-builder&donationFormID=' . $newPostID);
                exit();
            }
        }
    }
}

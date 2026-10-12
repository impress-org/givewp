<?php

namespace Give\Campaigns\Actions;

use Give\Campaigns\Models\Campaign;

/**
 * @since 4.1.0
 */
class PreventDeleteDefaultForm
{
    /**
      * @since TBD Number the placeholders and add translators comments.
     * @since 4.1.0
     */
    public function __invoke($postId)
    {
        if (get_post_type($postId) !== 'give_forms') {
            return;
        }

        $campaign = Campaign::findByFormId($postId);

        if ($campaign && $campaign->defaultFormId == $postId) {
            /* translators: 1: Form title, 2: Form ID */
            wp_die(sprintf(__('The form %1$s with ID %2$d cannot be deleted because it is the default form for a campaign.',
                'give'),
                $campaign->defaultForm()->title,
                $postId));
        }
    }

    /**
      * @since TBD Number the placeholders and add translators comments.
     * @since 4.1.0
     */
    public function preventTrashStatusChange($newStatus, $oldStatus, $post)
    {
        if ($newStatus === 'trash' && get_post_type($post->ID) === 'give_forms') {
            $campaign = Campaign::findByFormId($post->ID);


            if ($campaign && $campaign->defaultFormId == $post->ID) {
                wp_update_post([
                    'ID' => $post->ID,
                    'post_status' => $oldStatus,
                ]);

                /* translators: 1: Form title, 2: Form ID */
                wp_die(sprintf(__('The form %1$s with ID %2$d cannot be moved to trash because it is the default form for a campaign.',
                    'give'),
                    $campaign->defaultForm()->title,
                    $post->ID));
            }
        }
    }
}

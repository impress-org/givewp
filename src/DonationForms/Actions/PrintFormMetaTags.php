<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Helpers\Form\Utils;

/**
 * @since 3.17.0 updated to account for $post being null
 * @since 3.16.0
 */
class PrintFormMetaTags
{
    public function __invoke()
    {
        global $post;

        if (
            isset($post->post_type) &&
            $post->post_type === 'give_forms'
            && Utils::isV3Form($post->ID)
        ) {
            /** @var $form $form */
            $form = DonationForm::find($post->ID);

            // og:image
            if ($form && !empty($form->settings->designSettingsImageUrl)) {
                printf('<meta property="og:image" content="%s" />', esc_url($form->settings->designSettingsImageUrl));
            }
        }
    }
}

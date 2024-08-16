<?php

namespace Give\DonationForms\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\Helpers\Form\Utils;

/**
 * @unreleased
 */
class PrintFormMetaTags
{
    public function __invoke()
    {
        global $post;

        if (
            $post->post_type === 'give_forms'
            && Utils::isV3Form($post->ID)
        ) {
            $form = DonationForm::find($post->ID);

            // og:image
            if ( ! empty($form->settings->designSettingsImageUrl)) {
                printf('<meta property="og:image" content="%s" />', esc_url($form->settings->designSettingsImageUrl));
            }
        }
    }
}

<?php

namespace Give\FormBuilder\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class UpdateFormExcerpt
{
    /**
     * @unreleased
     *
     * @param DonationForm $form
     */
    public function __invoke(DonationForm $form)
    {
        wp_update_post([
            'ID' => $form->id,
            'post_excerpt' => $form->settings->formExcerpt,
        ]);
    }
}

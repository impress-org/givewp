<?php

namespace Give\FormTaxonomies\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class UpdateFormTags
{
    /**
     * @unreleased
     */
    public function __invoke(DonationForm $form)
    {
        if($form->settings->formTags) {
            wp_set_post_terms($form->id, array_column($form->settings->formTags, 'id'), 'give_forms_tag');
        }
    }
}

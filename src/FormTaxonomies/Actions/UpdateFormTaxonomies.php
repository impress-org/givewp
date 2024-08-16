<?php

namespace Give\FormTaxonomies\Actions;

use Give\DonationForms\Models\DonationForm;
use WP_REST_Request;

/**
 * @unreleased
 */
class UpdateFormTaxonomies
{
    /**
     * @unreleased
     */
    public function __invoke(DonationForm $form, WP_REST_Request $request)
    {
        $formBuilderSettings = json_decode($request->get_param('settings'), true);

        if(isset($formBuilderSettings['formTags'])) {
            wp_set_post_terms($form->id, array_column($formBuilderSettings['formTags'], 'id'), 'give_forms_tag');
        }

        if(isset($formBuilderSettings['formCategories'])) {
            wp_set_post_terms($form->id, $formBuilderSettings['formCategories'], 'give_forms_category');
        }
    }
}

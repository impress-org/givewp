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
            $formTags = $this->validateTermIds(array_column($formBuilderSettings['formTags'], 'id'));
            wp_set_object_terms($form->id, $formTags, 'give_forms_tag');
        }

        if(isset($formBuilderSettings['formCategories'])) {
            $formCategories = $this->validateTermIds($formBuilderSettings['formCategories']);
            wp_set_object_terms($form->id, $formCategories, 'give_forms_category');
        }
    }

    /**
     * @unreleased
     */
    public function validateTermIds(array $termsIds): array
    {
        return array_unique( array_map( 'intval', $termsIds ) );
    }
}

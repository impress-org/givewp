<?php

namespace Give\FormTaxonomies\Actions;

use Give\DonationForms\Models\DonationForm;

/**
 * @unreleased
 */
class UpdateFormTaxonomies
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @unreleased
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @unreleased
     */
    public function __invoke(DonationForm $form)
    {
        if(isset($this->settings['formTags'])) {
            wp_set_post_terms($form->id, array_column($this->settings['formTags'], 'id'), 'give_forms_tag');
        }

        if(isset($this->settings['formCategories'])) {
            wp_set_post_terms($form->id, $this->settings['formCategories'], 'give_forms_category');
        }
    }
}

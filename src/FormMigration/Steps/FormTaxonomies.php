<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @unreleased
 */
class FormTaxonomies extends FormMigrationStep
{
    /**
     * @unreleased
     */
    public function process()
    {
        if(taxonomy_exists('give_forms_tag')) {
            $this->migrateTaxonomy('give_forms_tag');
        }

        if (taxonomy_exists('give_forms_category')) {
            $this->migrateTaxonomy('give_forms_category');
        }
    }

    /**
     * @unreleased
     */
    public function migrateTaxonomy($taxonomy): void
    {
        $terms = get_terms(['post' => $this->formV2->id, 'taxonomy' => $taxonomy]);
        wp_set_post_terms($this->formV3->id, array_column($terms, 'term_id'), $taxonomy);
    }
}

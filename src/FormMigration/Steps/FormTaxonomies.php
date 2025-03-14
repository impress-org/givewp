<?php

namespace Give\FormMigration\Steps;

use Give\FormMigration\Contracts\FormMigrationStep;

/**
 * @since 3.16.0
 */
class FormTaxonomies extends FormMigrationStep
{
    /**
     * @since 3.16.0
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
     * @since 3.16.0
     */
    public function migrateTaxonomy($taxonomy): void
    {
        $terms = wp_get_post_terms($this->formV2->id, $taxonomy);
        wp_set_post_terms($this->formV3->id, array_column($terms, 'term_id'), $taxonomy);
    }
}

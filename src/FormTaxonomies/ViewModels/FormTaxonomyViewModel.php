<?php

namespace Give\FormTaxonomies\ViewModels;

/**
 * @since 3.16.0
 */
class FormTaxonomyViewModel
{
    /**
     * @since 3.16.0
     * @var int
     */
    protected $formId;

    /**
     * @since 3.16.0
     * @var array
     */
    protected $settings;

    /**
     * @since 3.16.0
     */
    public function __construct(int $formId, array $settings)
    {
        $this->formId = $formId;
        $this->settings = $settings;
    }

    /**
     * @since 3.16.0
     */
    public function isFormTagsEnabled(): bool
    {
        return give_is_setting_enabled($this->settings['tags']);
    }

    /**
     * @since 3.16.0
     */
    public function isFormCategoriesEnabled(): bool
    {
        return give_is_setting_enabled($this->settings['categories']);
    }

    /**
     * @since 3.16.0
     */
    public function getSelectedFormTags(): array
    {
        if(!$this->isFormTagsEnabled()) {
            return [];
        }

        $terms = wp_get_post_terms($this->formId, 'give_forms_tag');

        return array_map(function ($term) {
            return [
                'id' => $term->term_id,
                'value' => $term->name,
            ];
        }, $terms) ?? [];
    }

    /**
     * @since 3.16.0
     */
    public function getFormCategories(): array
    {
        if(!$this->isFormCategoriesEnabled()) {
            return [];
        }

        $terms = get_terms([
            'taxonomy' => 'give_forms_category',
            'hide_empty' => false,
        ]);

        return array_map(function ($term) {
            return [
                'id' => $term->term_id,
                'name' => $term->name,
                'parent' => $term->parent,
            ];
        }, $terms) ?? [];
    }

    /**
     * @since 3.16.0
     */
    public function getSelectedFormCategories(): array
    {
        if(!$this->isFormCategoriesEnabled()) {
            return [];
        }

        $terms = wp_get_post_terms($this->formId, 'give_forms_category');

        return array_map(function ($term) {
            return $term->term_id;
        }, $terms) ?? [];
    }
}

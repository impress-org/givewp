<?php

namespace Give\FormTaxonomies\ViewModels;

/**
 * @unreleased
 */
class FormTaxonomyViewModel
{
    /**
     * @unreleased
     * @var int
     */
    protected $formId;

    /**
     * @unreleased
     * @var array
     */
    protected $settings;

    /**
     * @unreleased
     */
    public function __construct(int $formId, array $settings)
    {
        $this->formId = $formId;
        $this->settings = $settings;
    }

    /**
     * @unreleased
     */
    public function isFormTagsEnabled(): bool
    {
        return give_is_setting_enabled($this->settings['tags']);
    }

    /**
     * @unreleased
     */
    public function isFormCategoriesEnabled(): bool
    {
        return give_is_setting_enabled($this->settings['categories']);
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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

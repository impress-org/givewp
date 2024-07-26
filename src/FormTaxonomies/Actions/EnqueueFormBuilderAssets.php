<?php

namespace Give\FormTaxonomies\Actions;

class EnqueueFormBuilderAssets
{
    public function __construct()
    {
        $this->settings = give_get_settings();
    }

    public function __invoke()
    {
        if($this->isFormTagsEnabled() || $this->isFormCategoriesEnabled()) {
            wp_enqueue_script('givewp-builder-taxonomy-settings', GIVE_PLUGIN_URL . 'assets/dist/js/form-taxonomy-settings.js');
            wp_add_inline_script('givewp-builder-taxonomy-settings','var giveTaxonomySettings =' . json_encode([
                    'formTagsEnabled' => $this->isFormTagsEnabled(),
                    'formCategoriesEnabled' => $this->isFormCategoriesEnabled(),
                    'formTags' => $this->getFormTags(),
                ]));
        }
    }

    public function isFormTagsEnabled()
    {
        return give_is_setting_enabled($this->settings['tags']);
    }

    public function isFormCategoriesEnabled()
    {
        return give_is_setting_enabled($this->settings['categories']);
    }

    public function getFormTags(): array
    {
        $terms = get_terms([
            // Form ID not provided by the hook, so we need to get it from the query string (if available).
            'post' => absint($_GET['donationFormID'] ?? 0),
            'taxonomy' => 'give_forms_tag',
            'hide_empty' => false,
        ]);

        return array_map(function ($tag) {
            return [
                'id' => $tag->term_id,
                'value' => $tag->name,
            ];
        }, $terms) ?? [];
    }
}

<?php

namespace Give\FormTaxonomies\Actions;

use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class EnqueueFormBuilderAssets
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->settings = give_get_settings();
    }

    /**
     * @unreleased
     */
    public function __invoke()
    {
        if($this->isFormTagsEnabled() || $this->isFormCategoriesEnabled()) {
            $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/formTaxonomySettings.asset.php');

            wp_enqueue_script(
                'givewp-builder-taxonomy-settings',
                GIVE_PLUGIN_URL . 'build/formTaxonomySettings.js',
                $scriptAsset['dependencies'],
                $scriptAsset['version'],
                true
            );
            wp_add_inline_script('givewp-builder-taxonomy-settings','var giveTaxonomySettings =' . json_encode([
                    'formTagsEnabled' => $this->isFormTagsEnabled(),
                    'formCategoriesEnabled' => $this->isFormCategoriesEnabled(),
                    'formTagsSelected' => $this->getSelectedFormTags(),
                    'formCategoriesAvailable' => $this->getFormCategories(),
                    'formCategoriesSelected' => $this->getSelectedFormCategories(),
                ]));
        }
    }

    /**
     * @unreleased
     */
    public function isFormTagsEnabled()
    {
        return give_is_setting_enabled($this->settings['tags']);
    }

    /**
     * @unreleased
     */
    public function isFormCategoriesEnabled()
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

        $terms = get_terms([
            // Form ID not provided by the hook, so we need to get it from the query string (if available).
            'post' => absint($_GET['donationFormID'] ?? 0),
            'taxonomy' => 'give_forms_tag',
        ]);

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
    public function getSelectedFormCategories()
    {
        if(!$this->isFormCategoriesEnabled()) {
            return [];
        }

        $terms = get_terms([
            // Form ID not provided by the hook, so we need to get it from the query string (if available).
            'post' => absint($_GET['donationFormID'] ?? 0),
            'taxonomy' => 'give_forms_category',
        ]);

        return array_map(function ($term) {
            return $term->term_id;
        }, $terms) ?? [];
    }
}

<?php

namespace Give\FormTaxonomies\Actions;

use Give\FormTaxonomies\ViewModels\FormTaxonomyViewModel;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;
use Give\Helpers\Language;

/**
 * @since 3.16.0
 */
class EnqueueFormBuilderAssets
{
    /**
     * @var FormTaxonomyViewModel
     */
    protected $viewModel;

    /**
     * @since 3.16.0
     */
    public function __construct(FormTaxonomyViewModel $viewModel)
    {
        $this->viewModel = $viewModel;
    }

    /**
     * @since 3.16.0
     */
    public function __invoke()
    {
        if($this->viewModel->isFormTagsEnabled() || $this->viewModel->isFormCategoriesEnabled()) {

            $scriptAsset = ScriptAsset::get(GIVE_PLUGIN_DIR . 'build/formTaxonomySettings.asset.php');

            wp_enqueue_script(
                'givewp-builder-taxonomy-settings',
                GIVE_PLUGIN_URL . 'build/formTaxonomySettings.js',
                $scriptAsset['dependencies'],
                $scriptAsset['version'],
                true
            );

            Language::setScriptTranslations('givewp-builder-taxonomy-settings');

            wp_enqueue_style(
                'givewp-builder-taxonomy-settings',
                GIVE_PLUGIN_URL . 'build/style-formTaxonomySettings.css'
            );

            wp_add_inline_script('givewp-builder-taxonomy-settings','var giveTaxonomySettings =' . json_encode([
                    'formTagsEnabled' => $this->viewModel->isFormTagsEnabled(),
                    'formCategoriesEnabled' => $this->viewModel->isFormCategoriesEnabled(),
                    'formTagsSelected' => $this->viewModel->getSelectedFormTags(),
                    'formCategoriesAvailable' => $this->viewModel->getFormCategories(),
                    'formCategoriesSelected' => $this->viewModel->getSelectedFormCategories(),
                ]));
        }
    }
}

<?php

namespace Give\FormTaxonomies\Actions;

use Give\FormTaxonomies\ViewModels\FormTaxonomyViewModel;
use Give\Framework\Support\Facades\Scripts\ScriptAsset;

/**
 * @unreleased
 */
class EnqueueFormBuilderAssets
{
    /**
     * @var FormTaxonomyViewModel
     */
    protected $viewModel;

    /**
     * @unreleased
     */
    public function __construct(FormTaxonomyViewModel $viewModel)
    {
        $this->viewModel = $viewModel;
    }

    /**
     * @unreleased
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

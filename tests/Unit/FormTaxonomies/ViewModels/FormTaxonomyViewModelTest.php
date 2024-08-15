<?php

namespace Give\Tests\Unit\FormTaxonomies\ViewModels;

use Give\FormTaxonomies\ViewModels\FormTaxonomyViewModel;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class FormTaxonomyViewModelTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    public function testIsFormTagsEnabled()
    {
        $form = $this->createSimpleDonationForm();

        give_update_option('tags', 'enabled');
        give_setup_taxonomies();

        $viewModel = new FormTaxonomyViewModel($form->id, give_get_settings());

        $this->assertTrue($viewModel->isFormTagsEnabled());
    }

    public function testIsFormCategoriesEnabled()
    {
        $form = $this->createSimpleDonationForm();

        give_update_option('categories', 'enabled');
        give_setup_taxonomies();

        $viewModel = new FormTaxonomyViewModel($form->id, give_get_settings());

        $this->assertTrue($viewModel->isFormCategoriesEnabled());
    }

    public function testGetSelectedFormTags()
    {
        $form = $this->createSimpleDonationForm();

        give_update_option('tags', 'enabled');
        give_setup_taxonomies();

        $tag = wp_create_term('aye', 'give_forms_tag');
        wp_set_post_terms($form->id, [$tag['term_id']], 'give_forms_tag');

        $viewModel = new FormTaxonomyViewModel($form->id, give_get_settings());

        $this->assertEquals([['id' => $tag['term_id'], 'value' => 'aye']], $viewModel->getSelectedFormTags());
    }

    public function testGetSelectedFormCategories()
    {
        $form = $this->createSimpleDonationForm();

        give_update_option('categories', 'enabled');
        give_setup_taxonomies();

        $category = wp_create_term('aye', 'give_forms_category');
        wp_set_post_terms($form->id, [$category['term_id']], 'give_forms_category');

        $viewModel = new FormTaxonomyViewModel($form->id, give_get_settings());

        $this->assertEquals([$category['term_id']], $viewModel->getSelectedFormCategories());
    }
}

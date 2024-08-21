<?php

namespace Give\Tests\Unit\FormTaxonomies\Actions;

use Give\DonationForms\Models\DonationForm;
use Give\FormTaxonomies\Actions\UpdateFormTaxonomies;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

/**
 * @unreleased
 */
class UpdateFormTaxonomiesTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    /**
     * @unreleased
     */
    public function testUpdatesFormTags()
    {
        give_update_option('tags', 'enabled');
        give_setup_taxonomies();

        $form = DonationForm::factory()->create();
        $request = new \WP_REST_Request();

        $tag = wp_create_term('aye', 'give_forms_tag');
        $request->set_param('settings', json_encode([
            'formTags' => [['id' => $tag['term_id']]],
        ]));

        (new UpdateFormTaxonomies)($form, $request);

        $terms = wp_get_post_terms($form->id, 'give_forms_tag');
        $this->assertEquals([$tag['term_id']], wp_list_pluck($terms, 'term_id'));
    }

    /**
     * @unreleased
     */
    public function testUpdatesFormCategory()
    {
        give_update_option('categories', 'enabled');
        give_setup_taxonomies();

        $form = DonationForm::factory()->create();
        $request = new \WP_REST_Request();

        $category = wp_create_term('aye', 'give_forms_category');
        $request->set_param('settings', json_encode([
            'formCategories' => [$category['term_id']],
        ]));

        (new UpdateFormTaxonomies)($form, $request);

        $terms = wp_get_post_terms($form->id, 'give_forms_category');
        $this->assertEquals([$category['term_id']], wp_list_pluck($terms, 'term_id'));
    }
}

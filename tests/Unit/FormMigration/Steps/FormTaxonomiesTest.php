<?php

namespace Give\Tests\Unit\FormMigration\Steps;

use Give\DonationForms\Models\DonationForm;
use Give\FormMigration\Actions\GetMigratedFormId;
use Give\FormMigration\DataTransferObjects\FormMigrationPayload;
use Give\FormMigration\Steps\FormTaxonomies;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give\Tests\Unit\DonationForms\TestTraits\LegacyDonationFormAdapter;

class FormTaxonomiesTest extends TestCase
{
    use RefreshDatabase;
    use LegacyDonationFormAdapter;

    public function setUp()
    {
        parent::setUp();

        give_update_option('tags', 'enabled');
        give_update_option('categories', 'enabled');
        give_setup_taxonomies();
    }

    public function testMigratesFormTags()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();

        $tag = wp_create_term('aye', 'give_forms_tag');
        wp_set_post_terms($donationFormV2->id, [$tag['term_id']], 'give_forms_tag');

        $step = new FormTaxonomies(
            new FormMigrationPayload($donationFormV2, $donationFormV3)
        );
        $step->process();

        $this->assertContains(
            $tag['term_id'],
            wp_list_pluck(get_the_terms($donationFormV3->id, 'give_forms_tag'), 'term_id')
        );
    }

    public function testMigratesFormCategories()
    {
        $donationFormV2 = $this->createSimpleDonationForm();
        $donationFormV3 = DonationForm::factory()->create();

        $category = wp_create_term('bee', 'give_forms_category');
        wp_set_post_terms($donationFormV2->id, [$category['term_id']], 'give_forms_category');

        $step = new FormTaxonomies(
            new FormMigrationPayload($donationFormV2, $donationFormV3)
        );
        $step->process();

        $this->assertContains(
            $category['term_id'],
            wp_list_pluck(get_the_terms($donationFormV3->id, 'give_forms_category'), 'term_id')
        );
    }
}

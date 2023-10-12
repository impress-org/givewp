<?php

namespace Give\Tests\Unit\CustomFields\Controllers;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\CustomFields\Controllers\DonationDetailsController;
use Give\Donations\CustomFields\Views\DonationDetailsView;
use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationDetailsController extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
     */
    public function testShowShouldReturnDonationDetailsViewRendered()
    {
        $donationForm = DonationForm::factory()->create();

        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        $controller = new DonationDetailsController();

        $fields = array_filter($donationForm->schema()->getFields(), static function ($field) {
            return $field->shouldShowInAdmin() && !$field->shouldStoreAsDonorMeta();
        });

        $view = new DonationDetailsView($donation, $fields);

        $this->assertSame($controller->show($donation->id), $view->render());
    }

    /**
     * @since 3.0.0
     */
    public function testShowShouldReturnEmptyStringIfDonationFormIsLegacy() {

        $legacyDonationForm = DB::table('posts')->insert([
            'post_title' => 'Legacy Donation Form',
            'post_type' => 'give_forms',
            'post_status' => 'publish',
        ]);

        $legacyDonationFormId = DB::last_insert_id();

        $donation = Donation::factory()->create([
            'formId' => $legacyDonationFormId,
        ]);

        $controller = new DonationDetailsController();

        $this->assertEmpty($controller->show($donation->id));
    }

}

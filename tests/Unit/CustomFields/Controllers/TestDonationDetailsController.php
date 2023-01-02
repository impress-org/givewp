<?php

namespace Give\Tests\Unit\CustomFields\Controllers;

use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\NextGen\CustomFields\Controllers\DonationDetailsController;
use Give\NextGen\CustomFields\Views\DonationDetailsView;
use Give\NextGen\DonationForm\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationDetailsController extends TestCase {
    use RefreshDatabase;

    /**
     * @unreleased
     */
    public function testShowShouldReturnDonationDetailsViewRendered() {
        $donationForm = DonationForm::factory()->create();

        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
        ]);

        $controller = new DonationDetailsController();

        $fields = array_filter($donationForm->schema()->getFields(), static function ($field) {
            return $field->shouldDisplayInAdmin() && !$field->shouldStoreAsDonorMeta();
        });

        $view = new DonationDetailsView($donation, $fields);

        $this->assertSame($controller->show($donation->id), $view->render());
    }

    /**
     * @unreleased
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

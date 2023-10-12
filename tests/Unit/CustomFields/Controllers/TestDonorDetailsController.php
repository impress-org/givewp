<?php

namespace Give\Tests\Unit\CustomFields\Controllers;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\Donations\Models\Donation;
use Give\Donors\CustomFields\Controllers\DonorDetailsController;
use Give\Donors\CustomFields\Views\DonorDetailsView;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonorDetailsController extends TestCase
{
    use RefreshDatabase;

    /**
     * @throws Exception
     */
    public function testShowShouldReturnDonorDetailsViewRendered()
    {
        $donationForm = DonationForm::factory()->create();
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create([
            'formId' => $donationForm->id,
            'donorId' => $donor->id,
        ]);

        $controller = new DonorDetailsController();

        $fields = array_reduce([$donationForm], static function ($fields, DonationForm $form) {
            return $fields + array_filter($form->schema()->getFields(), static function ($field) {
                    return $field->shouldShowInAdmin() && $field->shouldStoreAsDonorMeta();
                });
        }, []);

        $view = new DonorDetailsView($donor, $fields);

        $this->assertSame($controller->show($donor), $view->render());
    }

    /**
     * @since 3.0.0
     */
    public function testShowShouldReturnEmptyIfIsLegacyForm()
    {
        $legacyForm = DB::table('posts')->insert([
            'post_title' => 'Legacy Form',
            'post_type' => 'give_forms',
            'post_status' => 'publish',
        ]);

        $legacyFormId = DB::last_insert_id();

        $donor = Donor::factory()->create();

        $donation = Donation::factory()->create([
            'formId' => $legacyFormId,
            'donorId' => $donor->id,
        ]);

        $controller = new DonorDetailsController();

        $this->assertEmpty($controller->show($donor));
    }
}

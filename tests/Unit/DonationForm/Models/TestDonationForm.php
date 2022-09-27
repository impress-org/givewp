<?php

namespace TestsNextGen\Unit\DonationForm\Models;

use Exception;
use Give\NextGen\DonationForm\Models\DonationForm;
use GiveTests\TestCase;
use GiveTests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 */
class TestDonationForm extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertDonationForm()
    {
        $donationForm = DonationForm::factory()->create();

        $donationFromDatabase = DonationForm::find($donationForm->id);

        $this->assertEquals($donationForm->getAttributes(), $donationFromDatabase->getAttributes());
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testDonationFormShouldUpdate()
    {
        $donationForm = DonationForm::factory()->create([
            'title' => 'New Donation Form',
        ]);

        $donationForm->title = 'Updated Form Title';
        $donationForm->save();

        $donationFormFromDatabase = DonationForm::find($donationForm->id);

        $this->assertEquals('Updated Form Title', $donationFormFromDatabase->title);
    }
}

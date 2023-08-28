<?php

namespace Give\Tests\Unit\DonationForms\Models;

use Exception;
use Give\DonationForms\Models\DonationForm;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 3.0.0
 */
class TestDonationForm extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 3.0.0
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
     * @since 3.0.0
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

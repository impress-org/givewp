<?php

namespace Give\Tests\Unit\Donations\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donors\Models\Donor;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

class TestDonationNote extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 2.19.6
     *
     * @return void
     *
     * @throws Exception
     */
    public function testCreateShouldInsertDonationNote()
    {
        $donor = Donor::factory()->create();
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        $donationNote = DonationNote::create([
            'donationId' => $donation->id,
            'content' => 'im a note'
        ]);

        $donationNoteFromDatabase = DonationNote::find($donationNote->id);

        $this->assertEquals($donationNote->getAttributes(), $donationNoteFromDatabase->getAttributes());
    }

    /**
     * @since 2.19.6
     *
     * @return void
     * @throws Exception
     */
    public function testDonationNoteShouldGetDonation()
    {
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        /** @var DonationNote $donationNote */
        $donationNote = DonationNote::factory()->create(['donationId' => $donation->id]);

        $this->assertInstanceOf(Donation::class, $donationNote->donation);
        $this->assertEquals($donation->id, $donationNote->donation->id);
    }

    /**
     * @since 2.25.0
     *
     * @return void
     * @throws Exception
     */
    public function testDonationNoteTypeShouldAssignAdminAsDefault()
    {
        $donor = Donor::factory()->create();

        /** @var Donation $donation */
        $donation = Donation::factory()->create(['donorId' => $donor->id]);

        /** @var DonationNote $donationNote */
        $donationNote = DonationNote::factory()->create(['donationId' => $donation->id]);

        $this->assertTrue($donationNote->type->isAdmin());
    }
}

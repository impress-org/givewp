<?php

namespace unit\tests\Donations\Models;

use Exception;
use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donors\Models\Donor;
use Give\Framework\Database\DB;

class TestDonationNote  extends \Give_Unit_Test_Case
{
    /**
     * @unreleased
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationsTable = DB::prefix('posts');
        $donationMetaTable = DB::prefix('give_donationmeta');
        $donorTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');
        $notesTable = DB::prefix('give_comments');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $donationsTable");
        DB::query("TRUNCATE TABLE $notesTable");
    }

    /**
     * @unreleased
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
     * @unreleased
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
}

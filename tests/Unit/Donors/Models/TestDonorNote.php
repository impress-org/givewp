<?php

namespace Unit\Donors\Models;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\Models\DonorNote;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.4.0
 */
class TestDonorNote extends TestCase
{
    use RefreshDatabase;

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function testCreateShouldInsertDonorNote()
    {
        $donor = Donor::factory()->create();

        $donorNote = DonorNote::create([
            'donorId' => $donor->id,
            'content' => 'im a note',
        ]);

        $donorNoteFromDatabase = DonorNote::find($donorNote->id);

        $this->assertEquals($donorNote->getAttributes(), $donorNoteFromDatabase->getAttributes());
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function testDonorNoteShouldGetDonor()
    {
        $donor = Donor::factory()->create();

        /** @var DonorNote $donorNote */
        $donorNote = DonorNote::factory()->create(['donorId' => $donor->id]);

        $this->assertInstanceOf(Donor::class, $donorNote->donor);
        $this->assertEquals($donor->id, $donorNote->donor->id);
    }

    /**
     * @since 4.4.0
     *
     * @throws Exception
     */
    public function testDonorNoteTypeShouldAssignAdminAsDefault()
    {
        $donor = Donor::factory()->create();

        /** @var DonorNote $donorNote */
        $donorNote = DonorNote::factory()->create(['donorId' => $donor->id]);

        $this->assertTrue($donorNote->type->isAdmin());
    }
}

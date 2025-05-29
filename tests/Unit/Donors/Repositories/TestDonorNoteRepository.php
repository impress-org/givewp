<?php

namespace Unit\Donors\Repositories;

use Exception;
use Give\Donors\Models\Donor;
use Give\Donors\Models\DonorNote;
use Give\Donors\Repositories\DonorNotesRepository;
use Give\Donors\ValueObjects\DonorNoteType;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @coversDefaultClass DonorNotesRepository
 */
class TestDonorNoteRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testGetByIdShouldReturnDonorNote()
    {
        $donor = Donor::factory()->create();
        $donorNote = DonorNote::factory()->create(['donorId' => $donor->id]);
        $repository = new DonorNotesRepository();

        $donorNoteFromDatabase = $repository->getById($donorNote->id);

        $this->assertInstanceOf(DonorNote::class, $donorNoteFromDatabase);
        $this->assertEquals($donorNote->id, $donorNoteFromDatabase->id);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testInsertShouldAddDonorNoteToDatabase()
    {
        $donor = Donor::factory()->create();
        $donorNote = new DonorNote(
            ['donorId' => $donor->id, 'content' => 'im a note', 'type' => DonorNoteType::DONOR()]
        );

        $repository = new DonorNotesRepository();

        $repository->insert($donorNote);

        /** @var DonorNote $query */
        $query = $repository->prepareQuery()
            ->where('comment_ID', $donorNote->id)
            ->get();


        // simulate asserting database has values
        $this->assertInstanceOf(DonorNote::class, $donorNote);
        $this->assertEquals($query->id, $donorNote->id);
        $this->assertEquals($query->donorId, $donorNote->donorId);
        $this->assertEquals($query->content, $donorNote->content);
        $this->assertEquals($query->type, $donorNote->type);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenMissingKeyAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donorNoteMissingDonorId = new DonorNote([
            'content' => 'im a note',
        ]);

        $repository = new DonorNotesRepository();

        $repository->insert($donorNoteMissingDonorId);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenDonorDoesNotExistAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donorNoteWithInvalidDonor = new DonorNote([
            'donorId' => 10000,
            'content' => 'im a note',
        ]);

        $repository = new DonorNotesRepository();

        $repository->insert($donorNoteWithInvalidDonor);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpdateShouldFailValidationAndThrowException()
    {
        $this->expectException(InvalidArgumentException::class);

        $donorNoteMissingDonorId = new DonorNote([
            'content' => 'im a note',
        ]);

        $repository = new DonorNotesRepository();

        $repository->update($donorNoteMissingDonorId);
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testUpdateShouldUpdateDonorNoteValuesInTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $donorNote */
        $donorNote = DonorNote::factory()->create(
            ['donorId' => $donor->id, 'type' => DonorNoteType::ADMIN()]
        );

        $repository = new DonorNotesRepository();

        $donorNote->content = 'im an updated note';
        $donorNote->type = DonorNoteType::DONOR();

        // call update method
        $repository->update($donorNote);

        /** @var DonorNote $query */
        $query = $repository->prepareQuery()
            ->where('comment_ID', $donorNote->id)
            ->get();

        $this->assertEquals('im an updated note', $query->content);
        $this->assertTrue($query->type->isDonor());
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveDonorNoteFromTheDatabase()
    {
        /** @var Donor $donor */
        $donor = Donor::factory()->create();

        /** @var DonorNote $donorNote */
        $donorNote = DonorNote::factory()->create(['donorId' => $donor->id]);

        $repository = new DonorNotesRepository();

        $repository->delete($donorNote);

        /** @var DonorNote $query */
        $query = $repository->prepareQuery()
            ->where('comment_ID', $donor->id)
            ->get();

        $this->assertNull($query);
    }
}

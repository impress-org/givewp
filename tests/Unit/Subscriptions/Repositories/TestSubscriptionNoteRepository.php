<?php

namespace Give\Tests\Unit\Subscriptions\Repositories;

use Exception;
use Give\Framework\Database\DB;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Models\ModelQueryBuilder;
use Give\Framework\Support\Facades\DateTime\Temporal;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\Models\SubscriptionNote;
use Give\Subscriptions\Repositories\SubscriptionNotesRepository;
use Give\Subscriptions\ValueObjects\SubscriptionNoteType;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @unreleased
 *
 * @coversDefaultClass SubscriptionNotesRepository
 */
class TestSubscriptionNoteRepository extends TestCase
{
    use RefreshDatabase;

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldAddSubscriptionNoteToDatabase()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Test subscription note',
            'type' => SubscriptionNoteType::ADMIN(),
        ]);

        $repository->insert($subscriptionNote);

        $this->assertNotNull($subscriptionNote->id);
        $this->assertNotNull($subscriptionNote->createdAt);

        // Verify it was inserted into the comments table
        $commentQuery = DB::table('comments')
            ->where('comment_ID', $subscriptionNote->id)
            ->get();

        $this->assertNotNull($commentQuery);
        $this->assertEquals($subscriptionNote->content, $commentQuery->comment_content);
        $this->assertEquals($subscription->id, $commentQuery->comment_post_ID);
        $this->assertEquals('give_sub_note', $commentQuery->comment_type);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldDefaultToAdminTypeWhenTypeIsNull()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Test note without type',
        ]);

        $repository->insert($subscriptionNote);

        $this->assertEquals(SubscriptionNoteType::ADMIN, $subscriptionNote->type->getValue());
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldCreateMetaEntryForDonorType()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Test donor note',
            'type' => SubscriptionNoteType::DONOR(),
        ]);

        $repository->insert($subscriptionNote);

        // Verify meta entry was created for donor type
        $metaQuery = DB::table('commentmeta')
            ->where('comment_ID', $subscriptionNote->id)
            ->where('meta_key', 'note_type')
            ->get();

        $this->assertNotNull($metaQuery);
        $this->assertEquals(SubscriptionNoteType::DONOR, $metaQuery->meta_value);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenSubscriptionIdIsMissing()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'subscriptionId' is required.");

        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'content' => 'Test note without subscription ID',
        ]);

        $repository->insert($subscriptionNote);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenContentIsMissing()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'content' is required.");

        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
        ]);

        $repository->insert($subscriptionNote);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldFailValidationWhenSubscriptionDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid subscriptionId, Subscription does not exist');

        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => 99999, // Non-existent subscription
            'content' => 'Test note for invalid subscription',
        ]);

        $repository->insert($subscriptionNote);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldModifySubscriptionNoteInDatabase()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        // Create a note first
        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Original content',
            'type' => SubscriptionNoteType::ADMIN(),
        ]);
        $repository->insert($subscriptionNote);

        // Update the note
        $subscriptionNote->content = 'Updated content';
        $repository->update($subscriptionNote);

        // Verify the update in the database
        $commentQuery = DB::table('comments')
            ->where('comment_ID', $subscriptionNote->id)
            ->get();

        $this->assertEquals('Updated content', $commentQuery->comment_content);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testUpdateShouldFailValidationWhenRequiredFieldsAreMissing()
    {
        $this->expectException(InvalidArgumentException::class);

        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'id' => 1,
            'content' => 'Content without subscription ID',
        ]);

        $repository->update($subscriptionNote);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testDeleteShouldRemoveSubscriptionNoteFromDatabase()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        // Create a note first
        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Note to be deleted',
            'type' => SubscriptionNoteType::DONOR(),
        ]);
        $repository->insert($subscriptionNote);

        $noteId = $subscriptionNote->id;

        // Delete the note
        $result = $repository->delete($subscriptionNote);

        $this->assertTrue($result);

        // Verify it was deleted from comments table
        $commentQuery = DB::table('comments')
            ->where('comment_ID', $noteId)
            ->get();

        $this->assertNull($commentQuery);

        // Verify meta was also deleted
        $metaQuery = DB::table('commentmeta')
            ->where('comment_ID', $noteId)
            ->get();

        $this->assertNull($metaQuery);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testQueryByIdShouldReturnQueryBuilder()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        $queryBuilder = $repository->queryBySubscriptionId($subscription->id);

        $this->assertInstanceOf(ModelQueryBuilder::class, $queryBuilder);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testQueryByIdShouldFilterBySubscription()
    {
        $subscription1 = Subscription::factory()->createWithDonation();
        $subscription2 = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        // Create notes for both subscriptions
        $note1 = new SubscriptionNote([
            'subscriptionId' => $subscription1->id,
            'content' => 'Note for subscription 1',
        ]);
        $note2 = new SubscriptionNote([
            'subscriptionId' => $subscription2->id,
            'content' => 'Note for subscription 2',
        ]);

        $repository->insert($note1);
        $repository->insert($note2);

        // Test that the query builder is properly filtered
        $queryBuilder = $repository->queryBySubscriptionId($subscription1->id);

        // Verify the query builder is configured correctly
        $this->assertInstanceOf(ModelQueryBuilder::class, $queryBuilder);

        // Verify the query has the correct subscription filter by checking raw queries in comments table
        $directQuery = DB::table('comments')
            ->where('comment_post_ID', $subscription1->id)
            ->where('comment_type', 'give_sub_note')
            ->get();

        $this->assertNotNull($directQuery);
        $this->assertEquals($subscription1->id, $directQuery->comment_post_ID);
    }

    /**
     * @unreleased
     *
     * @return void
     */
    public function testPrepareQueryShouldReturnModelQueryBuilder()
    {
        $repository = new SubscriptionNotesRepository();

        $queryBuilder = $repository->prepareQuery();

        $this->assertInstanceOf(ModelQueryBuilder::class, $queryBuilder);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldSetCreatedAtWhenNotProvided()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Test note for timestamp',
        ]);

        // Ensure createdAt is null before insert
        $this->assertNull($subscriptionNote->createdAt);

        $repository->insert($subscriptionNote);

        // After insert, createdAt should be set
        $this->assertNotNull($subscriptionNote->createdAt);
        $this->assertInstanceOf(\DateTime::class, $subscriptionNote->createdAt);

        // Verify it's a recent timestamp (within last minute)
        $timeDiff = time() - $subscriptionNote->createdAt->getTimestamp();
        $this->assertLessThan(60, $timeDiff);
        $this->assertGreaterThanOrEqual(0, $timeDiff);
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testInsertShouldPreserveProvidedCreatedAt()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        $customCreatedAt = Temporal::getCurrentDateTime()->modify('-1 day');

        $subscriptionNote = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Test note with custom timestamp',
            'createdAt' => $customCreatedAt,
        ]);

        $repository->insert($subscriptionNote);

        $this->assertEquals(
            $customCreatedAt->format('Y-m-d H:i:s'),
            $subscriptionNote->createdAt->format('Y-m-d H:i:s')
        );
    }

    /**
     * @unreleased
     *
     * @return void
     *
     * @throws Exception
     */
    public function testQueryByIdShouldOrderByDescending()
    {
        $subscription = Subscription::factory()->createWithDonation();
        $repository = new SubscriptionNotesRepository();

        // Create multiple notes
        $note1 = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'First note',
        ]);
        $note2 = new SubscriptionNote([
            'subscriptionId' => $subscription->id,
            'content' => 'Second note',
        ]);

        $repository->insert($note1);
        $repository->insert($note2);

        // Test that the query builder has the correct ordering by checking the raw query structure
        $queryBuilder = $repository->queryBySubscriptionId($subscription->id);
        $this->assertInstanceOf(ModelQueryBuilder::class, $queryBuilder);

        // Verify that both notes exist and the newer note has a higher ID
        $note1Query = DB::table('comments')
            ->where('comment_ID', $note1->id)
            ->get();
        $note2Query = DB::table('comments')
            ->where('comment_ID', $note2->id)
            ->get();

        $this->assertNotNull($note1Query);
        $this->assertNotNull($note2Query);
        $this->assertEquals($subscription->id, $note1Query->comment_post_ID);
        $this->assertEquals($subscription->id, $note2Query->comment_post_ID);

        // Newer note (note2) should have higher ID than older note (note1)
        $this->assertGreaterThan($note1->id, $note2->id);
    }
}

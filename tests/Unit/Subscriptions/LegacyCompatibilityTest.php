<?php

namespace Give\Tests\Unit\Subscriptions;

use Exception;
use Give\Framework\Database\DB;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\Models\SubscriptionNote;
use Give\Subscriptions\ValueObjects\SubscriptionNoteType;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Temporary test class to verify compatibility between new SubscriptionNote
 * model/repository and legacy CRUD functions from give-recurring-functions.php
 *
 * @since 4.8.0
 */
class LegacyCompatibilityTest extends TestCase
{
    use RefreshDatabase;

        /**
     * @since 4.8.0
     */
    public function setUp(): void
    {
        parent::setUp();

        // Try to load legacy functions
        if (!function_exists('give_get_subscription_notes')) {
            $functionsFile = GIVE_PLUGIN_DIR . '../give-recurring/includes/give-recurring-functions.php';
            if (file_exists($functionsFile)) {
                require_once $functionsFile;
            }
        }

        // Skip entire class if required functions are not available
        if (!function_exists('give_get_subscription_notes') ||
            !function_exists('give_insert_subscription_note') ||
            !function_exists('give_delete_subscription_note')) {
            $this->markTestSkipped('Legacy recurring functions not available - give-recurring plugin may not be installed');
        }
    }

    /**
     * Test that notes created with legacy functions can be read by new model
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testLegacyToNewCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create note using legacy function
        $legacyNoteId = give_insert_subscription_note($subscription->id, 'Legacy note content');

        $this->assertNotFalse($legacyNoteId);
        $this->assertIsNumeric($legacyNoteId);

        // Read using new model
        $newModelNote = SubscriptionNote::find($legacyNoteId);

        $this->assertInstanceOf(SubscriptionNote::class, $newModelNote);
        $this->assertEquals($subscription->id, $newModelNote->subscriptionId);
        $this->assertEquals('Legacy note content', $newModelNote->content);
        $this->assertEquals(SubscriptionNoteType::ADMIN, $newModelNote->type->getValue());
    }

    /**
     * Test that notes created with new model can be read by legacy functions
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testNewToLegacyCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create note using new model
        $newModelNote = SubscriptionNote::create([
            'subscriptionId' => $subscription->id,
            'content' => 'New model note content',
            'type' => SubscriptionNoteType::ADMIN(),
        ]);

        $this->assertNotNull($newModelNote->id);

        // Read using legacy function
        $legacyNotes = give_get_subscription_notes($subscription->id);

        $this->assertIsArray($legacyNotes);
        $this->assertCount(1, $legacyNotes);

        $legacyNote = $legacyNotes[0];
        $this->assertEquals($newModelNote->id, $legacyNote->comment_ID);
        $this->assertEquals($subscription->id, $legacyNote->comment_post_ID);
        $this->assertEquals('New model note content', $legacyNote->comment_content);
        $this->assertEquals('give_sub_note', $legacyNote->comment_type);
    }

    /**
     * Test that both systems can coexist and work with each other's data
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testBidirectionalCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create notes using both methods
        $legacyNoteId = give_insert_subscription_note($subscription->id, 'Legacy note 1');

        $newModelNote = SubscriptionNote::create([
            'subscriptionId' => $subscription->id,
            'content' => 'New model note 1',
            'type' => SubscriptionNoteType::ADMIN(),
        ]);

        $legacyNoteId2 = give_insert_subscription_note($subscription->id, 'Legacy note 2');

        // Verify both systems can see all notes
        $legacyNotes = give_get_subscription_notes($subscription->id);
        $this->assertCount(3, $legacyNotes);

        $newModelNotes = SubscriptionNote::query()
            ->where('comments.comment_post_ID', $subscription->id)
            ->getAll();
        $this->assertCount(3, $newModelNotes);

        // Verify content matches
        $legacyContents = array_map(function($note) {
            return $note->comment_content;
        }, $legacyNotes);

        $newModelContents = array_map(function($note) {
            return $note->content;
        }, $newModelNotes);

        sort($legacyContents);
        sort($newModelContents);

        $this->assertEquals($legacyContents, $newModelContents);
    }

    /**
     * Test that deletion works across both systems
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testDeletionCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create note with legacy function
        $legacyNoteId = give_insert_subscription_note($subscription->id, 'Note to delete');

        // Verify it exists in new model
        $noteFromNewModel = SubscriptionNote::find($legacyNoteId);
        $this->assertInstanceOf(SubscriptionNote::class, $noteFromNewModel);

        // Delete using legacy function
        $deleteResult = give_delete_subscription_note($legacyNoteId, $subscription->id);
        $this->assertTrue($deleteResult);

        // Verify it's gone from new model
        $deletedNote = SubscriptionNote::find($legacyNoteId);
        $this->assertNull($deletedNote);

        // Test reverse: create with new model, delete with legacy
        $newModelNote = SubscriptionNote::create([
            'subscriptionId' => $subscription->id,
            'content' => 'Another note to delete',
            'type' => SubscriptionNoteType::ADMIN(),
        ]);

        // Delete using legacy function
        $deleteResult2 = give_delete_subscription_note($newModelNote->id, $subscription->id);
        $this->assertTrue($deleteResult2);

        // Verify it's gone
        $deletedNote2 = SubscriptionNote::find($newModelNote->id);
        $this->assertNull($deletedNote2);
    }

    /**
     * Test that donor notes work correctly with both systems
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testDonorNoteTypeCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create donor note using new model
        $donorNote = SubscriptionNote::create([
            'subscriptionId' => $subscription->id,
            'content' => 'Donor note content',
            'type' => SubscriptionNoteType::DONOR(),
        ]);

        // Verify the meta entry exists for donor type
        $metaQuery = DB::table('commentmeta')
            ->where('comment_ID', $donorNote->id)
            ->where('meta_key', 'note_type')
            ->get();

        $this->assertNotNull($metaQuery);
        $this->assertEquals(SubscriptionNoteType::DONOR, $metaQuery->meta_value);

        // Verify legacy functions can read it
        if (function_exists('give_get_subscription_notes')) {
            $legacyNotes = give_get_subscription_notes($subscription->id);
            $this->assertCount(1, $legacyNotes);

            $legacyNote = $legacyNotes[0];
            $this->assertEquals('Donor note content', $legacyNote->comment_content);
        }
    }

    /**
     * Test that admin notes (no meta) work correctly with both systems
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testAdminNoteTypeCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create admin note using legacy function (no meta should be created)
        $legacyNoteId = give_insert_subscription_note($subscription->id, 'Admin note content');

        // Verify no meta entry exists (admin is default)
        $metaQuery = DB::table('commentmeta')
            ->where('comment_ID', $legacyNoteId)
            ->where('meta_key', 'note_type')
            ->get();

        $this->assertNull($metaQuery);

        // Verify new model reads it as admin type
        $noteFromNewModel = SubscriptionNote::find($legacyNoteId);
        $this->assertEquals(SubscriptionNoteType::ADMIN, $noteFromNewModel->type->getValue());
    }

    /**
     * Test that search functionality works across both systems
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testSearchCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create notes with searchable content
        give_insert_subscription_note($subscription->id, 'This note contains keyword SEARCHABLE');
        give_insert_subscription_note($subscription->id, 'This note does not contain the term');
        give_insert_subscription_note($subscription->id, 'Another SEARCHABLE note here');

        // Test legacy search functionality
        $searchResults = give_get_subscription_notes($subscription->id, 'SEARCHABLE');
        $this->assertCount(2, $searchResults);

        foreach ($searchResults as $note) {
            $this->assertStringContainsString('SEARCHABLE', $note->comment_content);
        }
    }

    /**
     * Test that date handling is consistent between both systems
     *
     * @since 4.8.0
     *
     * @throws Exception
     */
    public function testDateHandlingCompatibility()
    {
        $subscription = Subscription::factory()->createWithDonation();

        // Create note with legacy function
        $legacyNoteId = give_insert_subscription_note($subscription->id, 'Date test note');

        // Check that new model can read the date
        $noteFromNewModel = SubscriptionNote::find($legacyNoteId);
        $this->assertInstanceOf(\DateTime::class, $noteFromNewModel->createdAt);

        // Verify the date is recent (within last minute)
        $timeDiff = time() - $noteFromNewModel->createdAt->getTimestamp();
        $this->assertLessThan(60, $timeDiff);
        $this->assertGreaterThanOrEqual(0, $timeDiff);

        // Create note with new model
        $newModelNote = SubscriptionNote::create([
            'subscriptionId' => $subscription->id,
            'content' => 'New model date test',
            'type' => SubscriptionNoteType::ADMIN(),
        ]);

        // Verify legacy functions can read the date
        if (function_exists('give_get_subscription_notes')) {
            $legacyNotes = give_get_subscription_notes($subscription->id);
            $newModelNoteFromLegacy = null;

            foreach ($legacyNotes as $note) {
                if ($note->comment_ID == $newModelNote->id) {
                    $newModelNoteFromLegacy = $note;
                    break;
                }
            }

            $this->assertNotNull($newModelNoteFromLegacy);
            $this->assertNotEmpty($newModelNoteFromLegacy->comment_date);

            // Verify date formats are compatible
            $legacyDate = strtotime($newModelNoteFromLegacy->comment_date);
            $newModelDate = $newModelNote->createdAt->getTimestamp();

            // Should be within a few seconds of each other
            $this->assertLessThan(5, abs($legacyDate - $newModelDate));
        }
    }
}

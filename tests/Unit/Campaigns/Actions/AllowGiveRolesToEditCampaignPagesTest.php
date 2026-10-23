<?php

namespace Give\Tests\Unit\Campaigns\Actions;

use Give\Campaigns\Actions\AllowGiveRolesToEditCampaignPages;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignPageMetaKeys;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use ReflectionClass;
use WP_User;

/**
 * @since 4.14.0
 *
 * @covers \Give\Campaigns\Actions\AllowGiveRolesToEditCampaignPages
 */
final class AllowGiveRolesToEditCampaignPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AllowGiveRolesToEditCampaignPages
     */
    private AllowGiveRolesToEditCampaignPages $action;

    /**
     * @since 4.14.0
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->action = new AllowGiveRolesToEditCampaignPages();
        $this->clearStaticCache();
    }

    /**
     * @since 4.14.0
     */
    public function tearDown(): void
    {
        $this->clearStaticCache();
        parent::tearDown();
    }

    /**
     * Clear the static caches between tests.
     *
     * @since 4.19.0.1 Reset the resolved post ID statics along with the campaign page cache.
     */
    private function clearStaticCache(): void
    {
        $reflection = new ReflectionClass(AllowGiveRolesToEditCampaignPages::class);
        $property = $reflection->getProperty('campaignPageCache');
        $property->setAccessible(true);
        $property->setValue(null, []);

        $resolved = $reflection->getProperty('editingPostId');
        $resolved->setAccessible(true);
        $resolved->setValue(null, null);

        $resolvedFlag = $reflection->getProperty('editingPostIdResolved');
        $resolvedFlag->setAccessible(true);
        $resolvedFlag->setValue(null, false);
    }

    /**
     * @since 4.14.0
     */
    public function testMapMetaCapGrantsAccessForCampaignPageWhenUserHasEditGiveFormsCapability(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user (has edit_give_forms capability)
        $userId = self::factory()->user->create(['role' => 'give_worker']);

        // Call mapMetaCap for edit_post
        $result = $this->action->mapMetaCap(
            ['edit_others_pages'], // Original required caps
            'edit_post',
            $userId,
            [$page->id]
        );

        // Should return empty array to grant access
        $this->assertEmpty($result);
    }

    /**
     * @since 4.14.0
     */
    public function testMapMetaCapDoesNotGrantAccessForNonCampaignPage(): void
    {
        // Create a regular page (not a campaign page)
        $pageId = self::factory()->post->create(['post_type' => 'page']);

        // Create a give_worker user
        $userId = self::factory()->user->create(['role' => 'give_worker']);

        $originalCaps = ['edit_others_pages'];

        // Call mapMetaCap for edit_post
        $result = $this->action->mapMetaCap(
            $originalCaps,
            'edit_post',
            $userId,
            [$pageId]
        );

        // Should return original caps (not modified)
        $this->assertEquals($originalCaps, $result);
    }

    /**
     * @since 4.14.0
     */
    public function testMapMetaCapDoesNotGrantAccessWhenUserLacksEditGiveFormsCapability(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a subscriber user (no edit_give_forms capability)
        $userId = self::factory()->user->create(['role' => 'subscriber']);

        $originalCaps = ['edit_others_pages'];

        // Call mapMetaCap for edit_post
        $result = $this->action->mapMetaCap(
            $originalCaps,
            'edit_post',
            $userId,
            [$page->id]
        );

        // Should return original caps (not modified)
        $this->assertEquals($originalCaps, $result);
    }

    /**
     * @since 4.14.0
     */
    public function testMapMetaCapIgnoresNonPageCapabilities(): void
    {
        $userId = self::factory()->user->create(['role' => 'give_worker']);

        $originalCaps = ['some_other_cap'];

        // Call mapMetaCap for a non-page capability
        $result = $this->action->mapMetaCap(
            $originalCaps,
            'manage_options',
            $userId,
            []
        );

        // Should return original caps unchanged
        $this->assertEquals($originalCaps, $result);
    }

    /**
     * @since 4.14.0
     */
    public function testMapMetaCapHandlesPublishPostCapability(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user
        $userId = self::factory()->user->create(['role' => 'give_worker']);

        // Call mapMetaCap for publish_post
        $result = $this->action->mapMetaCap(
            ['publish_pages'],
            'publish_post',
            $userId,
            [$page->id]
        );

        // Should return empty array to grant access
        $this->assertEmpty($result);
    }

    /**
     * @since 4.19.0.1 Establish the edit context with the global post instead of the query string.
     * @since 4.14.0
     */
    public function testGrantPublishCapabilityGrantsCapsForCampaignPage(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user and get WP_User object
        $userId = self::factory()->user->create(['role' => 'give_worker']);
        $user = new WP_User($userId);

        // Simulate being in admin context editing the campaign page
        $GLOBALS['post'] = get_post($page->id);
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should have publish_pages granted
        $this->assertTrue($result['publish_pages']);

        // Clean up
        unset($GLOBALS['post']);
    }

    /**
     * @since 4.19.0.1 Establish the edit context with the global post instead of the query string.
     * @since 4.14.0
     */
    public function testGrantPublishCapabilityGrantsMultipleCaps(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user and get WP_User object
        $userId = self::factory()->user->create(['role' => 'give_worker']);
        $user = new WP_User($userId);

        // Simulate being in admin context editing the campaign page
        $GLOBALS['post'] = get_post($page->id);
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages', 'edit_others_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should have both caps granted
        $this->assertTrue($result['publish_pages']);
        $this->assertTrue($result['edit_others_pages']);

        // Clean up
        unset($GLOBALS['post']);
    }

    /**
     * @since 4.19.0.1 Establish the edit context with the global post instead of the query string.
     * @since 4.14.0
     */
    public function testGrantPublishCapabilityDoesNotGrantCapsForNonCampaignPage(): void
    {
        // Create a regular page (not a campaign page)
        $pageId = self::factory()->post->create(['post_type' => 'page']);

        // Create a give_worker user and get WP_User object
        $userId = self::factory()->user->create(['role' => 'give_worker']);
        $user = new WP_User($userId);

        // Simulate being in admin context editing the regular page
        $GLOBALS['post'] = get_post($pageId);
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should NOT have publish_pages granted (it wasn't there before)
        $this->assertArrayNotHasKey('publish_pages', $result);

        // Clean up
        unset($GLOBALS['post']);
    }

    /**
     * @since 4.19.0.1 Establish the edit context with the global post instead of the query string.
     * @since 4.14.0
     */
    public function testGrantPublishCapabilityDoesNotGrantCapsWhenUserLacksEditGiveFormsCapability(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a subscriber user (no edit_give_forms capability)
        $userId = self::factory()->user->create(['role' => 'subscriber']);
        $user = new WP_User($userId);

        // Simulate being in admin context editing the campaign page
        $GLOBALS['post'] = get_post($page->id);
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should NOT have publish_pages granted
        $this->assertArrayNotHasKey('publish_pages', $result);

        // Clean up
        unset($GLOBALS['post']);
    }

    /**
     * The grant must be decided from the page actually being edited (the REST
     * route or the global $post), never from a post ID in the query string —
     * otherwise a request could attach the grant to a page it is not editing.
     *
     * @since 4.19.0.1
     */
    public function testGrantPublishCapabilityDoesNotGrantCapsFromQueryStringPostId(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user and get WP_User object
        $userId = self::factory()->user->create(['role' => 'give_worker']);
        $user = new WP_User($userId);

        // Admin context, but only the query string points at a campaign page
        // while no page is actually being edited in this request.
        $_GET['post'] = $page->id;
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should NOT have publish_pages granted
        $this->assertArrayNotHasKey('publish_pages', $result);

        // Clean up
        unset($_GET['post']);
    }

    /**
     * An early capability check that runs before the edit screen sets the
     * global $post must not lock in a missing resolution for the whole request —
     * the grant still applies once the edit context appears.
     *
     * @since 4.19.0.1
     */
    public function testGrantPublishCapabilityResolvesPageIdWhenContextAppearsLater(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user and get WP_User object
        $userId = self::factory()->user->create(['role' => 'give_worker']);
        $user = new WP_User($userId);

        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages'];
        $args = ['publish_pages', $userId];

        // First check runs before any page context exists (e.g. during admin_init).
        $early = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);
        $this->assertArrayNotHasKey('publish_pages', $early);

        // The edit screen now establishes the page being edited.
        $GLOBALS['post'] = get_post($page->id);

        $later = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // The grant must apply to the page the screen is actually editing.
        $this->assertTrue($later['publish_pages']);

        // Clean up
        unset($GLOBALS['post']);
    }

    /**
     * @since 4.14.0
     */
    public function testCampaignPageCacheIsUsed(): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user
        $userId = self::factory()->user->create(['role' => 'give_worker']);

        // First call - should query database
        $result1 = $this->action->mapMetaCap(
            ['edit_others_pages'],
            'edit_post',
            $userId,
            [$page->id]
        );

        // Delete the meta to prove cache is used (if it re-queried, it would fail)
        delete_post_meta($page->id, CampaignPageMetaKeys::CAMPAIGN_ID);

        // Second call - should use cache
        $result2 = $this->action->mapMetaCap(
            ['edit_others_pages'],
            'edit_post',
            $userId,
            [$page->id]
        );

        // Both should return empty array (access granted)
        $this->assertEmpty($result1);
        $this->assertEmpty($result2);
    }

    /**
     * A third-party filter on get_post_metadata that runs a capability check re-enters this action
     * through map_meta_cap. The campaign page lookup must not read its meta through that filter.
     *
     * @since 4.16.7
     */
    public function testCampaignPageLookupIsUnaffectedByPostMetaFilters(): void
    {
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();
        $userId = self::factory()->user->create(['role' => 'give_worker']);

        add_filter('map_meta_cap', [$this->action, 'mapMetaCap'], 10, 4);

        $depth = 0;
        $maxDepth = 0;
        $metaFilter = static function ($value, $objectId, $metaKey) use ($userId, &$depth, &$maxDepth) {
            if ($metaKey !== CampaignPageMetaKeys::CAMPAIGN_ID) {
                return $value;
            }

            $depth++;
            $maxDepth = max($maxDepth, $depth);

            /* Cap the nesting so a regression fails the assertion instead of exhausting the stack. */
            if ($depth < 10) {
                user_can($userId, 'edit_post', $objectId);
            }

            $depth--;

            return $value;
        };
        add_filter('get_post_metadata', $metaFilter, 10, 3);

        $result = $this->action->mapMetaCap(['edit_others_pages'], 'edit_post', $userId, [$page->id]);

        remove_filter('get_post_metadata', $metaFilter, 10);
        remove_filter('map_meta_cap', [$this->action, 'mapMetaCap'], 10);

        $this->assertSame(0, $maxDepth, 'The campaign ID meta was read through get_post_metadata.');
        $this->assertEmpty($result);
    }

    /**
     * @since 4.14.0
     *
     * @dataProvider pageMetaCapabilitiesProvider
     */
    public function testMapMetaCapHandlesAllPageMetaCapabilities(string $metaCap): void
    {
        // Create a campaign with a page
        $campaign = Campaign::factory()->create();
        $page = $campaign->page();

        // Create a give_worker user
        $userId = self::factory()->user->create(['role' => 'give_worker']);

        // Call mapMetaCap for the meta capability
        $result = $this->action->mapMetaCap(
            ['some_required_cap'],
            $metaCap,
            $userId,
            [$page->id]
        );

        // Should return empty array to grant access
        $this->assertEmpty($result, "Failed for meta capability: $metaCap");
    }

    /**
     * @since 4.14.0
     */
    public function pageMetaCapabilitiesProvider(): array
    {
        return [
            'edit_post' => ['edit_post'],
            'delete_post' => ['delete_post'],
            'publish_post' => ['publish_post'],
            'read_post' => ['read_post'],
        ];
    }
}


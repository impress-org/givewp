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
 * @unreleased
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
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->action = new AllowGiveRolesToEditCampaignPages();
        $this->clearStaticCache();
    }

    /**
     * @unreleased
     */
    public function tearDown(): void
    {
        $this->clearStaticCache();
        parent::tearDown();
    }

    /**
     * Clear the static cache between tests.
     */
    private function clearStaticCache(): void
    {
        $reflection = new ReflectionClass(AllowGiveRolesToEditCampaignPages::class);
        $property = $reflection->getProperty('campaignPageCache');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
        $_GET['post'] = $page->id;
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should have publish_pages granted
        $this->assertTrue($result['publish_pages']);

        // Clean up
        unset($_GET['post']);
    }

    /**
     * @unreleased
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
        $_GET['post'] = $page->id;
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages', 'edit_others_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should have both caps granted
        $this->assertTrue($result['publish_pages']);
        $this->assertTrue($result['edit_others_pages']);

        // Clean up
        unset($_GET['post']);
    }

    /**
     * @unreleased
     */
    public function testGrantPublishCapabilityDoesNotGrantCapsForNonCampaignPage(): void
    {
        // Create a regular page (not a campaign page)
        $pageId = self::factory()->post->create(['post_type' => 'page']);

        // Create a give_worker user and get WP_User object
        $userId = self::factory()->user->create(['role' => 'give_worker']);
        $user = new WP_User($userId);

        // Simulate being in admin context editing the regular page
        $_GET['post'] = $pageId;
        set_current_screen('edit');

        $allcaps = $user->allcaps;
        $caps = ['publish_pages'];
        $args = ['publish_pages', $userId];

        $result = $this->action->grantPublishCapability($allcaps, $caps, $args, $user);

        // Should NOT have publish_pages granted (it wasn't there before)
        $this->assertArrayNotHasKey('publish_pages', $result);

        // Clean up
        unset($_GET['post']);
    }

    /**
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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
     * @unreleased
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


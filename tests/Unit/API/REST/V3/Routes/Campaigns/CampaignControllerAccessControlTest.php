<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Campaigns;

use Faker\Factory;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * Tests for SVUL-74: Private page access control in CampaignController::get_item().
 *
 * Covers Fix 2:
 * - When a campaign's WordPress page has post_status = 'private', non-admins are blocked.
 * - Admins can always access campaigns regardless of page privacy.
 *
 * Note: inactive campaign tests (draft, archived) already exist in CampaignControllerItemTest.
 *
 * @since TBD
 */
final class CampaignControllerAccessControlTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    private function buildItemRoute(int $campaignId): string
    {
        return '/' . CampaignRoute::NAMESPACE . '/' . str_replace('(?P<id>[0-9]+)', $campaignId, CampaignRoute::CAMPAIGN);
    }

    private function createPrivatePage(): int
    {
        $faker = Factory::create();

        return wp_insert_post([
            'post_title'  => $faker->sentence(3),
            'post_status' => 'private',
            'post_type'   => 'page',
        ]);
    }

    private function createPublicPage(): int
    {
        $faker = Factory::create();

        return wp_insert_post([
            'post_title'  => $faker->sentence(3),
            'post_status' => 'publish',
            'post_type'   => 'page',
        ]);
    }

    // --- Private campaign page: GET /campaigns/{id} ---

    /**
     * @since TBD
     */
    public function testUnauthenticatedUserCannotAccessActiveCampaignWithPrivatePage(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPrivatePage();
        $campaign->save();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->buildItemRoute($campaign->id))
        );

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @since TBD
     */
    public function testAuthenticatedNonAdminCannotAccessActiveCampaignWithPrivatePage(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPrivatePage();
        $campaign->save();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->buildItemRoute($campaign->id), [], 'subscriber')
        );

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @since TBD
     */
    public function testAdminCanAccessActiveCampaignWithPrivatePage(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPrivatePage();
        $campaign->save();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->buildItemRoute($campaign->id), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Admin can access even when both campaign is inactive AND page is private.
     *
     * @since TBD
     */
    public function testAdminCanAccessInactiveCampaignWithPrivatePage(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);
        $campaign->pageId = $this->createPrivatePage();
        $campaign->save();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->buildItemRoute($campaign->id), [], 'administrator')
        );

        $this->assertEquals(200, $response->get_status());
    }

    // --- Sanity check ---

    /**
     * @since TBD
     */
    public function testAnyoneCanAccessActiveCampaignWithPublicPage(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPublicPage();
        $campaign->save();

        $response = $this->dispatchRequest(
            $this->createRequest(WP_REST_Server::READABLE, $this->buildItemRoute($campaign->id))
        );

        $this->assertEquals(200, $response->get_status());
    }
}

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
 * Tests for SVUL-74: Access control checks in CampaignCommentsController::get_items().
 *
 * Covers Fix 1:
 * - Inactive campaigns are blocked for non-admins (401 unauthenticated, 403 authenticated).
 * - Active campaigns with a private WordPress page are blocked for non-admins.
 * - Admins bypass both restrictions.
 *
 * @unreleased
 */
final class CampaignCommentsAccessControlTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    private function buildRoute(int $campaignId): string
    {
        return '/' . CampaignRoute::NAMESPACE . '/campaigns/' . $campaignId . '/comments';
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

    // --- Inactive campaign ---

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotViewCommentsOnDraftCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotViewCommentsOnArchivedCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testAuthenticatedNonAdminCannotViewCommentsOnDraftCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'subscriber');
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @unreleased
     */
    public function testAdminCanViewCommentsOnDraftCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::DRAFT()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'administrator');
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }

    /**
     * @unreleased
     */
    public function testAdminCanViewCommentsOnArchivedCampaign(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ARCHIVED()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'administrator');
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }

    // --- Private campaign page ---

    /**
     * @unreleased
     */
    public function testUnauthenticatedUserCannotViewCommentsWhenCampaignPageIsPrivate(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPrivatePage();
        $campaign->save();

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testAuthenticatedNonAdminCannotViewCommentsWhenCampaignPageIsPrivate(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPrivatePage();
        $campaign->save();

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'subscriber');
        $request->set_param('id', $campaign->id);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(403, $response->get_status());
        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @unreleased
     */
    public function testAdminCanViewCommentsWhenCampaignPageIsPrivate(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPrivatePage();
        $campaign->save();

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id), [], 'administrator');
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }

    // --- Sanity checks ---

    /**
     * @unreleased
     */
    public function testAnyoneCanViewCommentsOnActiveCampaignWithNoPage(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }

    /**
     * @unreleased
     */
    public function testAnyoneCanViewCommentsOnActiveCampaignWithPublicPage(): void
    {
        $campaign = Campaign::factory()->create(['status' => CampaignStatus::ACTIVE()]);
        $campaign->pageId = $this->createPublicPage();
        $campaign->save();

        $request = $this->createRequest(WP_REST_Server::READABLE, $this->buildRoute($campaign->id));
        $request->set_param('id', $campaign->id);

        $this->assertEquals(200, $this->dispatchRequest($request)->get_status());
    }
}

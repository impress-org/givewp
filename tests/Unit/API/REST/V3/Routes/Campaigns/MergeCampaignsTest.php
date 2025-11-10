<?php

namespace Unit\API\REST\V3\Routes\Campaigns;

use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\Models\Campaign;
use Give\Tests\RestApiTestCase;
use WP_REST_Request;

/**
 * @since 4.0.0
 */
class MergeCampaignsTest extends RestApiTestCase
{
    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testMergeCampaignsRouteShouldReturnErrorForNotAdminUsers()
    {
        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();
        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();
        /** @var Campaign $destinationCampaign */
        $destinationCampaign = Campaign::factory()->create();

        $route = '/' . CampaignRoute::NAMESPACE . "/campaigns/$destinationCampaign->id/merge";
        $request = new WP_REST_Request('PUT', $route);
        $request->set_query_params(
            [
                'id' => $destinationCampaign->id,
                'campaignsToMergeIds' => [$campaign1->id, $campaign2->id],
            ]
        );

        $response = $this->dispatchRequest($request);
        $errorCode = $response->get_status();

        $this->assertEquals(401, $errorCode);
    }

    /**
     * @unreleased updated to assert return data
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function testMergeCampaignsRouteShouldReturnDestinationCampaignDataForAdminUsers()
    {
        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();
        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();
        /** @var Campaign $destinationCampaign */
        $destinationCampaign = Campaign::factory()->create();

        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'admin38974238473824',
                'user_pass' => 'admin38974238473824',
                'user_email' => 'admin38974238473824@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        $route = '/' . CampaignRoute::NAMESPACE . "/campaigns/$destinationCampaign->id/merge";
        $request = new WP_REST_Request('PUT', $route);
        $request->set_query_params(
            [
                'id' => $destinationCampaign->id,
                'campaignsToMergeIds' => [$campaign1->id, $campaign2->id],
            ]
        );

        $response = $this->dispatchRequest($request);

        // Assert status OK and destination campaign data is returned
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('id', $data);
        $this->assertSame($destinationCampaign->id, $data['id']);

        // Ensure merged campaigns no longer exist
        $this->assertNull(Campaign::find($campaign1->id));
        $this->assertNull(Campaign::find($campaign2->id));
    }

    /**
     * @unreleased
     */
    public function testMergeCampaignsRouteShouldReturnNotFoundWhenDestinationMissing()
    {
        /** @var Campaign $campaign1 */
        $campaign1 = Campaign::factory()->create();
        /** @var Campaign $campaign2 */
        $campaign2 = Campaign::factory()->create();

        $newAdminUser = $this->factory()->user->create(
            [
                'role' => 'administrator',
                'user_login' => 'admin_missing_dest',
                'user_pass' => 'admin_missing_dest',
                'user_email' => 'admin_missing_dest@test.com',
            ]
        );
        wp_set_current_user($newAdminUser);

        $invalidDestinationId = 999999;
        $route = '/' . CampaignRoute::NAMESPACE . "/campaigns/$invalidDestinationId/merge";
        $request = new WP_REST_Request('PUT', $route);
        $request->set_query_params(
            [
                'id' => $invalidDestinationId,
                'campaignsToMergeIds' => [$campaign1->id, $campaign2->id],
            ]
        );

        $response = $this->dispatchRequest($request);

        // Expect WP_Error with code campaign_not_found and 404 status
        $this->assertErrorResponse('campaign_not_found', $response, 404);
    }
}

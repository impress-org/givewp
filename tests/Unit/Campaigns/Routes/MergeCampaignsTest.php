<?php

namespace Unit\Campaigns\Routes;

use Exception;
use Give\Campaigns\Models\Campaign;
use Give\Tests\RestApiTestCase;
use WP_REST_Request;

/**
 * @unreleased
 */
class MergeCampaignsTest extends RestApiTestCase
{
    /**
     * @unreleased
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

        $request = new WP_REST_Request('PUT', "/give-api/v2/campaigns/$destinationCampaign->id/merge");
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
     * @unreleased
     *
     * @throws Exception
     */
    public function testMergeCampaignsRouteShouldReturnTrueForAdminUsers()
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

        $request = new WP_REST_Request('PUT', "/give-api/v2/campaigns/$destinationCampaign->id/merge");
        $request->set_query_params(
            [
                'id' => $destinationCampaign->id,
                'campaignsToMergeIds' => [$campaign1->id, $campaign2->id],
            ]
        );

        $response = $this->dispatchRequest($request);
        $merged = $response->get_data();

        $this->assertTrue($merged);
    }
}

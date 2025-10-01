<?php

namespace Give\Tests\Unit\DonationForms\Routes;

use Give\DonationForms\ValueObjects\DonationFormsRoute;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonationFormRouteAssociateWithCampaignTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that unauthenticated users cannot associate forms with campaigns via POST /forms/associate-with-campaign.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCannotAssociateFormsWithCampaign()
    {
        $route = '/' . DonationFormsRoute::NAMESPACE . '/' . DonationFormsRoute::ASSOCIATE_FORMS_WITH_CAMPAIGN;
        $request = $this->createRequest(WP_REST_Server::CREATABLE, $route);
        $request->set_body_params([
            'formIDs' => [1, 2, 3],
            'campaignId' => 1
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * Test that admin users can associate forms with campaigns via POST /forms/associate-with-campaign.
     *
     * @unreleased
     */
    public function testAdminUserCanAssociateFormsWithCampaign()
    {
        $route = '/' . DonationFormsRoute::NAMESPACE . '/' . DonationFormsRoute::ASSOCIATE_FORMS_WITH_CAMPAIGN;
        $request = $this->createRequest(WP_REST_Server::CREATABLE, $route, [], 'administrator');
        $request->set_body_params([
            'formIDs' => [1, 2, 3],
            'campaignId' => 1
        ]);

        $response = $this->dispatchRequest($request);

        // Note: Admin users should be able to access this endpoint successfully
        // The important thing is that it's not blocked by permissions (401/403)
        $this->assertNotEquals(401, $response->get_status());
        $this->assertNotEquals(403, $response->get_status());
    }

    /**
     * Test that non-admin users cannot associate forms with campaigns via POST /forms/associate-with-campaign.
     *
     * @unreleased
     */
    public function testNonAdminUserCannotAssociateFormsWithCampaign()
    {
        $route = '/' . DonationFormsRoute::NAMESPACE . '/' . DonationFormsRoute::ASSOCIATE_FORMS_WITH_CAMPAIGN;
        $request = $this->createRequest(WP_REST_Server::CREATABLE, $route, [], 'subscriber');
        $request->set_body_params([
            'formIDs' => [1, 2, 3],
            'campaignId' => 1
        ]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(403, $response->get_status());
    }
}

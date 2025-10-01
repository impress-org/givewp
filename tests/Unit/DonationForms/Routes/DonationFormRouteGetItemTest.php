<?php

namespace Give\Tests\Unit\DonationForms\Routes;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonationFormRouteGetItemTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that unauthenticated users cannot access individual non-published form via GET /forms/{id}.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCannotAccessNonPublishedForm()
    {
        $draftForm = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);

        $route = '/givewp/v3/forms/' . $draftForm->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access individual published form via GET /forms/{id}.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserCanAccessPublishedForm()
    {
        $publishedForm = DonationForm::factory()->create(['status' => DonationFormStatus::PUBLISHED()]);

        $route = '/givewp/v3/forms/' . $publishedForm->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that admin users can access individual non-published form via GET /forms/{id}.
     *
     * @unreleased
     */
    public function testAdminUserCanAccessNonPublishedForm()
    {
        $draftForm = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);

        $route = '/givewp/v3/forms/' . $draftForm->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that admin users can access individual private form via GET /forms/{id}.
     *
     * @unreleased
     */
    public function testAdminUserCanAccessPrivateForm()
    {
        $privateForm = DonationForm::factory()->create(['status' => DonationFormStatus::PRIVATE()]);

        $route = '/givewp/v3/forms/' . $privateForm->id;
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that unauthenticated users get 404 for non-existent form.
     *
     * @unreleased
     */
    public function testUnauthenticatedUserGets404ForNonExistentForm()
    {
        $route = '/givewp/v3/forms/999';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(404, $response->get_status());
    }
}

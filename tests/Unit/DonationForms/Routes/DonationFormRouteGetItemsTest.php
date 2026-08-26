<?php

namespace Give\Tests\Unit\DonationForms\Routes;

use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Server;

/**
 * @since 4.10.1
 */
class DonationFormRouteGetItemsTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * Test that unauthenticated users cannot access non-published forms via GET /forms.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCannotAccessNonPublishedForms()
    {
        // Create forms with different statuses
        $publishedForm = DonationForm::factory()->create(['status' => DonationFormStatus::PUBLISHED()]);
        $draftForm = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);
        $privateForm = DonationForm::factory()->create(['status' => DonationFormStatus::PRIVATE()]);

        $route = '/givewp/v3/forms';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['draft', 'private']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access published forms via GET /forms.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCanAccessPublishedForms()
    {
        $publishedForm = DonationForm::factory()->create(['status' => DonationFormStatus::PUBLISHED()]);

        $route = '/givewp/v3/forms';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['publish']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that unauthenticated users can access forms without status filter via GET /forms.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCanAccessFormsWithoutStatusFilter()
    {
        $publishedForm = DonationForm::factory()->create(['status' => DonationFormStatus::PUBLISHED()]);

        $route = '/givewp/v3/forms';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that admin users can access all form statuses via GET /forms.
     *
     * @since 4.10.1
     */
    public function testAdminUserCanAccessAllFormStatuses()
    {
        $publishedForm = DonationForm::factory()->create(['status' => DonationFormStatus::PUBLISHED()]);
        $draftForm = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);
        $privateForm = DonationForm::factory()->create(['status' => DonationFormStatus::PRIVATE()]);

        $route = '/givewp/v3/forms';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route, [], 'administrator');
        $request->set_query_params(['status' => ['publish', 'draft', 'private']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(200, $response->get_status());
    }

    /**
     * Test that mixed status requests are blocked for unauthenticated users.
     *
     * @since 4.10.1
     */
    public function testUnauthenticatedUserCannotAccessMixedStatusRequests()
    {
        $publishedForm = DonationForm::factory()->create(['status' => DonationFormStatus::PUBLISHED()]);
        $draftForm = DonationForm::factory()->create(['status' => DonationFormStatus::DRAFT()]);

        $route = '/givewp/v3/forms';
        $request = $this->createRequest(WP_REST_Server::READABLE, $route);
        $request->set_query_params(['status' => ['publish', 'draft']]);

        $response = $this->dispatchRequest($request);

        $this->assertEquals(401, $response->get_status());
    }

}

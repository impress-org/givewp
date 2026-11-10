<?php

namespace Give\Tests\Unit\Framework\ListTable\FilterOptions;

use Give\Campaigns\Models\Campaign;
use Give\Donors\Models\Donor;
use Give\Framework\ListTable\FilterOptions\ListTableFilterOptionsController;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since TBD
 */
class ListTableFilterOptionsControllerTest extends RestApiTestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->refreshDatabase();
    }

    /**
     * @since TBD
     */
    public function testPathHasNoSource(): void
    {
        $this->assertSame('/givewp/v3/list-table-filter-options', ListTableFilterOptionsController::path());
    }

    /**
     * @since TBD
     */
    public function testRejectsASignedOutRequestWith401(): void
    {
        wp_set_current_user(0);

        $this->assertErrorResponse('rest_forbidden', $this->dispatch('campaign'), 401);
    }

    /**
     * @since TBD
     */
    public function testRejectsAnUnknownScreenWith404(): void
    {
        $this->actAs('administrator');

        $response = $this->dispatch('campaign', ['screen' => 'settings']);

        $this->assertErrorResponse('give_list_table_unknown_screen', $response, 404);
    }

    /**
     * @since TBD
     */
    public function testRejectsASourceTheScreenDoesNotOfferWith400(): void
    {
        $this->actAs('administrator');

        $response = $this->dispatch('form', ['screen' => 'donors']);

        $this->assertErrorResponse('give_list_table_invalid_filter_source', $response, 400);
    }

    /**
     * @since TBD
     */
    public function testRejectsAUserWhoCannotViewTheScreenWith403(): void
    {
        $this->actAs('give_worker');

        $response = $this->dispatch('campaign', ['screen' => 'donors']);

        $this->assertErrorResponse('rest_forbidden', $response, 403);
    }

    /**
     * @since TBD
     */
    public function testWithoutScreenUsesTheSourcePermission(): void
    {
        $this->actAs('give_worker');
        $this->assertErrorResponse('rest_forbidden', $this->dispatch('donor'), 403);

        $this->actAs('subscriber');
        $this->assertErrorResponse('rest_forbidden', $this->dispatch('campaign'), 403);

        $this->actAs('administrator');
        $this->assertSame(200, $this->dispatch('donor')->get_status());
    }

    /**
     * @since TBD
     */
    public function testReturnsOptionsAndHasMore(): void
    {
        $this->actAs('administrator');
        $first = Campaign::factory()->create(['title' => 'Zed A'])->id;
        Campaign::factory()->create(['title' => 'Zed B']);

        $response = $this->dispatch('campaign', ['screen' => 'donations', 'search' => 'zed', 'page' => 1]);

        $this->assertSame(200, $response->get_status());
        $this->assertSame(
            [
                'options' => [
                    ['value' => (string)$first, 'label' => 'Zed A'],
                    ['value' => (string)($first + 1), 'label' => 'Zed B'],
                ],
                'hasMore' => false,
            ],
            $response->get_data()
        );
    }

    /**
     * @since TBD
     */
    public function testIncludeIgnoresSearchAndPaging(): void
    {
        $this->actAs('administrator');
        $id = Donor::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@example.com'])->id;

        $response = $this->dispatch('donor', ['include' => [$id, 999999], 'search' => 'nomatch', 'page' => 7]);

        $this->assertSame(
            ['options' => [['value' => (string)$id, 'label' => 'Jane Doe']], 'hasMore' => false],
            $response->get_data()
        );
        $this->assertStringNotContainsString('@', (string)wp_json_encode($response->get_data()));
    }

    /**
     * @since TBD
     */
    public function testIncludeAcceptsAtMostFifty(): void
    {
        $this->actAs('administrator');

        $this->assertSame(200, $this->dispatch('donor', ['include' => range(1, 50)])->get_status());
        $this->assertErrorResponse('rest_invalid_param', $this->dispatch('donor', ['include' => range(1, 51)]), 400);
    }

    /**
     * @since TBD
     */
    public function testRejectsBadSearchAndPageParameters(): void
    {
        $this->actAs('administrator');

        $this->assertErrorResponse('rest_invalid_param', $this->dispatch('donor', ['search' => str_repeat('a', 101)]), 400);
        $this->assertErrorResponse('rest_invalid_param', $this->dispatch('donor', ['page' => 0]), 400);
        $this->assertErrorResponse('rest_invalid_param', $this->dispatch('donor', ['include' => [0]]), 400);
    }

    /**
     * A page past the limit would overflow the offset and give page one back.
     *
     * @since TBD
     */
    public function testAcceptsPagesUpToTenThousandAndRejectsLarger(): void
    {
        $this->actAs('administrator');

        $response = $this->dispatch('donor', ['page' => 10000]);

        $this->assertSame(200, $response->get_status());
        $this->assertSame([], $response->get_data()['options']);
        $this->assertErrorResponse('rest_invalid_param', $this->dispatch('donor', ['page' => 10001]), 400);
        $this->assertErrorResponse(
            'rest_invalid_param',
            $this->dispatch('donor', ['page' => '9223372036854775807']),
            400
        );
    }

    /**
     * @since TBD
     */
    public function testRejectsAnUnknownSourceWith404(): void
    {
        $this->actAs('administrator');

        $request = new WP_REST_Request('GET', ListTableFilterOptionsController::path() . '/email');

        $this->assertSame(404, $this->dispatchRequest($request)->get_status());
    }

    /**
     * @since TBD
     */
    private function actAs(string $role): void
    {
        wp_set_current_user(self::factory()->user->create(['role' => $role]));
    }

    /**
     * @since TBD
     */
    private function dispatch(string $source, array $params = []): WP_REST_Response
    {
        $request = new WP_REST_Request('GET', ListTableFilterOptionsController::path() . '/' . $source);
        $request->set_query_params($params);

        return $this->dispatchRequest($request);
    }
}

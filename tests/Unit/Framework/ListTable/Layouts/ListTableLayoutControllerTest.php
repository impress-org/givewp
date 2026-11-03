<?php

namespace Give\Tests\Unit\Framework\ListTable\Layouts;

use Give\Framework\ListTable\Layouts\ListTableLayoutStore;
use Give\Tests\RestApiTestCase;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @since TBD
 */
class ListTableLayoutControllerTest extends RestApiTestCase
{
    private const ROUTE = '/givewp/v3/list-table-layouts/';

    /**
     * @since TBD
     */
    public function testRejectsASignedOutRequestWith401(): void
    {
        wp_set_current_user(0);

        $response = $this->dispatch('GET', 'donations');

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @since TBD
     */
    public function testRejectsAnUnknownScreenWith404(): void
    {
        wp_set_current_user($this->createUser('administrator'));

        $response = $this->dispatch('GET', 'settings');

        $this->assertErrorResponse('give_list_table_unknown_screen', $response, 404);
    }

    /**
     * @since TBD
     */
    public function testRejectsAUserWithoutTheScreenCapabilityWith403(): void
    {
        wp_set_current_user($this->createUser('give_worker'));

        $response = $this->dispatch('PUT', 'donors', ['version' => 1, 'density' => 'compact']);

        $this->assertErrorResponse('rest_forbidden', $response, 403);
        $this->assertNull(give(ListTableLayoutStore::class)->get(get_current_user_id(), 'donors'));
    }

    /**
     * @since TBD
     */
    public function testAllowsAUserWithTheScreenCapability(): void
    {
        wp_set_current_user($this->createUser('give_worker'));

        $response = $this->dispatch('GET', 'donations');

        $this->assertSame(200, $response->get_status());
    }

    /**
     * @since TBD
     */
    public function testGetReturnsNullWhenNothingIsStored(): void
    {
        wp_set_current_user($this->createUser('administrator'));

        $response = $this->dispatch('GET', 'donations');

        $this->assertSame(200, $response->get_status());
        $this->assertSame(['screen' => 'donations', 'layout' => null], $response->get_data());
    }

    /**
     * @since TBD
     */
    public function testPutStoresTheNormalizedLayoutAndReturnsIt(): void
    {
        wp_set_current_user($this->createUser('administrator'));

        $response = $this->dispatch('PUT', 'donations', [
            'perPage' => 50,
            'density' => 'compact',
            'visibility' => ['form' => false],
            'version' => 1,
        ]);

        $expected = [
            'version' => 1,
            'visibility' => ['form' => false],
            'density' => 'compact',
            'perPage' => 50,
        ];

        $this->assertSame(200, $response->get_status());
        $this->assertSame(['screen' => 'donations', 'layout' => $expected], $response->get_data());
        $this->assertSame($expected, $this->dispatch('GET', 'donations')->get_data()['layout']);
    }

    /**
     * @since TBD
     */
    public function testPutReplacesTheWholeRecord(): void
    {
        wp_set_current_user($this->createUser('administrator'));

        $this->dispatch('PUT', 'donations', ['version' => 1, 'density' => 'compact', 'perPage' => 50]);
        $this->dispatch('PUT', 'donations', ['version' => 1, 'density' => 'comfortable']);

        $this->assertSame(
            ['version' => 1, 'density' => 'comfortable'],
            $this->dispatch('GET', 'donations')->get_data()['layout']
        );
    }

    /**
     * @since TBD
     */
    public function testPutRejectsAnInvalidLayoutWith400AndWritesNothing(): void
    {
        wp_set_current_user($this->createUser('administrator'));

        $this->dispatch('PUT', 'donations', ['version' => 1, 'density' => 'compact']);
        $response = $this->dispatch('PUT', 'donations', ['version' => 1, 'search' => 'jane']);

        $this->assertErrorResponse('give_list_table_invalid_layout', $response, 400);
        $this->assertSame(
            ['version' => 1, 'density' => 'compact'],
            $this->dispatch('GET', 'donations')->get_data()['layout']
        );
    }

    /**
     * @since TBD
     */
    public function testPutRejectsFiftyOneEntries(): void
    {
        wp_set_current_user($this->createUser('administrator'));

        $ids = array_map(static function (int $index): string {
            return "column-{$index}";
        }, range(1, 51));

        $response = $this->dispatch('PUT', 'donations', ['version' => 1, 'order' => $ids]);

        $this->assertErrorResponse('give_list_table_invalid_layout', $response, 400);
    }

    /**
     * @since TBD
     */
    public function testDeleteRemovesTheRecord(): void
    {
        wp_set_current_user($this->createUser('administrator'));

        $this->dispatch('PUT', 'donations', ['version' => 1, 'density' => 'compact']);
        $response = $this->dispatch('DELETE', 'donations');

        $this->assertSame(200, $response->get_status());
        $this->assertSame(['screen' => 'donations', 'layout' => null], $response->get_data());
        $this->assertNull($this->dispatch('GET', 'donations')->get_data()['layout']);
    }

    /**
     * @since TBD
     */
    public function testAUserCanNeitherReadNorChangeAnotherUsersRecord(): void
    {
        $userA = $this->createUser('administrator');
        $userB = $this->createUser('administrator');

        wp_set_current_user($userA);
        $this->dispatch('PUT', 'donations', ['version' => 1, 'density' => 'compact']);

        wp_set_current_user($userB);
        $this->assertNull($this->dispatch('GET', 'donations')->get_data()['layout']);
        $this->dispatch('PUT', 'donations', ['version' => 1, 'density' => 'comfortable']);
        $this->dispatch('DELETE', 'donations');

        $this->assertSame(
            ['version' => 1, 'density' => 'compact'],
            give(ListTableLayoutStore::class)->get($userA, 'donations')
        );
    }

    /**
     * @since TBD
     */
    public function testOneHundredWritesLeaveOneRecord(): void
    {
        global $wpdb;

        $userId = $this->createUser('administrator');
        wp_set_current_user($userId);

        foreach (range(1, 100) as $perPage) {
            $this->dispatch('PUT', 'donations', ['version' => 1, 'perPage' => $perPage]);
        }

        $rows = (int)$wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key = %s",
                $userId,
                $wpdb->get_blog_prefix() . 'give_list_table_layout_donations'
            )
        );

        $this->assertSame(1, $rows);
        $this->assertSame(
            ['version' => 1, 'perPage' => 100],
            $this->dispatch('GET', 'donations')->get_data()['layout']
        );
    }

    /**
     * @since TBD
     */
    private function createUser(string $role): int
    {
        return self::factory()->user->create(['role' => $role]);
    }

    /**
     * @since TBD
     */
    private function dispatch(string $method, string $screen, ?array $body = null): WP_REST_Response
    {
        $request = new WP_REST_Request($method, self::ROUTE . $screen);

        if ($body !== null) {
            $request->set_header('Content-Type', 'application/json');
            $request->set_body(wp_json_encode($body));
        }

        return $this->dispatchRequest($request);
    }
}

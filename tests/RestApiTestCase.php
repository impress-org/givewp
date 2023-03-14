<?php
namespace Give\Tests;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Roles;
use WP_UnitTest_Factory;

/**
 * Give Unit API Test Case
 *
 * Provides Give-specific setup/tear down/assert methods
 * and helper functions to be used in API tests.
 *
 * @unreleased
 */
class RestApiTestCase extends TestCase
{
    /**
     * Test REST Server
     *
     * @unreleased
     *
     * @var WP_REST_Server
     */
    protected $server;

    /**
     * Test user accounts for API authentication
     *
     * @unreleased
     *
     * @var int[] $users
     */
    protected static $users = [
        'anonymous' => null,
        'administrator' => null,
        'editor' => null,
        'author' => null,
        'contributor' => null,
        'subscriber' => null,
    ];

    /**
     * Create users for API authentication
     *
     * @unreleased
     *
     * @return void
     */
    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        self::$users = [
            'anonymous' => 0,
            'administrator' => $factory->user->create(['role' => 'administrator']),
            'editor' => $factory->user->create(['role' => 'editor']),
            'author' => $factory->user->create(['role' => 'author']),
            'contributor' => $factory->user->create(['role' => 'contributor']),
            'subscriber' => $factory->user->create(['role' => 'subscriber']),
        ];
    }

    /**
     * Initialize the REST server
     *
     * @unreleased
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        global $wp_rest_server;
        $this->server = $wp_rest_server = new WP_REST_Server;
        do_action('rest_api_init');

        $this->flushRoles();
    }

    /**
     * Wrapper for creating a request
     *
     * @unreleased
     *
     * @param string $method
     * @param string $route
     * @param array  $attributes
     * @param string $authentication
     *
     * @return WP_REST_Request
     */
    public function createRequest(
        string $method,
        string $route,
        array $attributes = [],
        string $authentication = 'anonymous'
    ): WP_REST_Request {
        if ( ! in_array($authentication, array_keys(self::$users), true)) {
            $authentication = 'anonymous';
        }
        wp_set_current_user(self::$users[$authentication]);

        return new WP_REST_Request($method, $route, $attributes);
    }

    /**
     * Wrapper for dispatching a request
     *
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function dispatchRequest(WP_REST_Request $request): WP_REST_Response
    {
        return $this->server->dispatch($request);
    }

    /**
     * Flushes the WordPress user roles and reloads them from the database.
     *
     * This function ensures that we are testing against the database data, not just in-memory data.
     *
     * @unreleased
     *
     * @return void
     */
    private function flushRoles()
    {
        unset($GLOBALS['wp_user_roles']);
        global $wp_roles;
        $wp_roles = new WP_Roles();
    }

    /**
     * Destroy the REST server
     *
     * @unreleased
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        global $wp_rest_server;
        $wp_rest_server = null;
    }

    /**
     * Asserts that the response is a WP error response with the specified code and status (if provided).
     *
     * @unreleased
     *
     * @param $code
     * @param $response
     * @param $status
     *
     * @return void
     */
    protected function assertErrorResponse($code, $response, $status = null)
    {
        if (is_a($response, 'WP_REST_Response')) {
            $response = $response->as_error();
        }

        $this->assertInstanceOf('WP_Error', $response);
        $this->assertEquals($code, $response->get_error_code());

        if (null !== $status) {
            $data = $response->get_error_data();
            $this->assertArrayHasKey('status', $data);
            $this->assertEquals($status, $data['status']);
        }
    }
}

<?php
namespace Give\Tests;

use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Roles;
use WP_UnitTest_Factory;
use WP_User;

/**
 * Give Unit API Test Case
 *
 * Provides Give-specific setup/tear down/assert methods
 * and helper functions to be used in API tests.
 *
 * @unreleased
 */
class ApiTestCase extends TestCase
{
    /**
     * Test REST Server
     *
     * @var WP_REST_Server
     */
    protected $server;

    /**
     * Test user accounts for API authentication
     *
     * @var WP_User[] $users
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
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        global $wp_rest_server;
        $this->server = $wp_rest_server = new WP_REST_Server;
        do_action('rest_api_init');

        $this->flush_roles();
    }

    /**
     * Wrapper for creating a request
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
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function dispatchRequest(WP_REST_Request $request): WP_REST_Response
    {
        return $this->server->dispatch($request);
    }

    private function flush_roles()
    {
        unset($GLOBALS['wp_user_roles']);
        global $wp_roles;
        $wp_roles = new WP_Roles();
    }

    /**
     * Destroy the REST server
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();

        global $wp_rest_server;
        $wp_rest_server = null;
    }
}

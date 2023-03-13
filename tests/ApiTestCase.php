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
     * Test admin user account for API authentication
     *
     * @var WP_User
     */
    protected static $admin_user_id;

    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        self::$admin_user_id = $factory->user->create([
            'role' => 'administrator',
        ]);
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
     * @param bool   $authenticated
     *
     * @return WP_REST_Request
     */
    public function createRequest(
        string $method,
        string $route,
        array $attributes = [],
        bool $authenticated = true
    ): WP_REST_Request {
        if ($authenticated) {
            wp_set_current_user(self::$admin_user_id);
        } else {
            wp_set_current_user(0);
        }

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

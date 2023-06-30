<?php
namespace Give\Tests;

use Give\Tests\TestTraits\HasDefaultGiveWPUsers;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
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
 * @since 2.26.0
 */
class RestApiTestCase extends TestCase
{
    /**
     * Test REST Server
     *
     * @since 2.26.0
     *
     * @var WP_REST_Server
     */
    protected $server;

    /**
     * Test user accounts for API authentication
     *
     * @since 2.26.0
     *
     * @var int[] $users
     */
    protected static $users = [
        'anonymous' => 0,
    ];

    /**
     * Create users for API authentication
     *
     * @since 2.26.0
     *
     * @return void
     */
    public static function wpSetUpBeforeClass(WP_UnitTest_Factory $factory)
    {
        $uses = array_flip(class_uses(static::class));

        if (isset($uses[HasDefaultWordPressUsers::class])) {
            self::$users = array_merge(
                self::$users,
                static::createDefaultWordPressUsers($factory)
            );
        }

        if (isset($uses[HasDefaultGiveWPUsers::class])) {
            self::$users = array_merge(
                self::$users,
                static::createDefaultGiveWPUsers($factory)
            );
        }
    }

    /**
     * Initialize the REST server
     *
     * @since 2.26.0
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
     * Flushes the WordPress user roles and reloads them from the database.
     *
     * This function ensures that we are testing against the database data, not just in-memory data.
     *
     * @since 2.26.0
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
     * @since 2.26.0
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
     * Delete users after the test class is done
     *
     * @since 2.26.0
     *
     * @return void
     */
    public static function wpTearDownAfterClass()
    {
        foreach (self::$users as $userRole => $userId) {
            if (wp_delete_user($userId)) {
                unset(self::$users[$userRole]);
            }
        }
    }

    /**
     * Wrapper for creating a request
     *
     * @since 2.26.0
     *
     * @param string $method     The HTTP method of the request (e.g. GET, POST, etc.).
     * @param string $route      The REST API route to access (e.g. /wp/v2/posts).
     * @param array  $attributes Optional. An array of attributes to set on the request object.
     * @param string $userRole   Optional. The role of the user to authenticate the request (e.g. anonymous, administrator, etc.).
     *
     * @return WP_REST_Request A new WP_REST_Request object with the specified parameters.
     */
    public function createRequest(
        string $method,
        string $route,
        array $attributes = [],
        string $userRole = 'anonymous'
    ): WP_REST_Request {
        if ( ! in_array($userRole, array_keys(self::$users), true)) {
            $userRole = 'anonymous';
        }
        wp_set_current_user(self::$users[$userRole]);

        return new WP_REST_Request($method, $route, $attributes);
    }

    /**
     * Wrapper for dispatching a request
     *
     * @since 2.26.0
     *
     * @param WP_REST_Request $request The request object to dispatch to the server.
     *
     * @return WP_REST_Response The response object returned by the server.
     */
    public function dispatchRequest(WP_REST_Request $request): WP_REST_Response
    {
        return $this->server->dispatch($request);
    }

    /**
     * Asserts that the response is a WP error response with the specified code and status (if provided).
     *
     * @since 2.26.0
     *
     * @param int|string $code     The expected error code of the WP_Error object.
     * @param mixed      $response The response object to check (can be a WP_REST_Response or WP_Error object).
     * @param int|null   $status   Optional. The expected error status of the WP_Error object (if any).
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

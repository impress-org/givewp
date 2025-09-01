<?php

namespace Give\Tests\Unit\API\REST\V3\Routes\Subscriptions;

use Give\API\REST\V3\Routes\Subscriptions\SubscriptionNotesController;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Subscriptions\Models\Subscription;
use Give\Tests\RestApiTestCase;
use Give\Tests\TestTraits\HasDefaultWordPressUsers;
use Give\Tests\TestTraits\RefreshDatabase;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 *
 * @coversDefaultClass SubscriptionNotesController
 */
class SubscriptionNoteRouteTest extends RestApiTestCase
{
    use RefreshDatabase;
    use HasDefaultWordPressUsers;

    /**
     * @unreleased
     *
     * @var SubscriptionNotesController
     */
    private $controller;

    /**
     * @unreleased
     *
     * @var Subscription
     */
    private $subscription;

    /**
     * @unreleased
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new SubscriptionNotesController();
        $this->subscription = Subscription::factory()->createWithDonation();

        // Register the routes
        $this->controller->register_routes();
    }

    /**
     * @unreleased
     */
    public function testControllerInstantiation()
    {
        $controller = new SubscriptionNotesController();

        $this->assertInstanceOf(SubscriptionNotesController::class, $controller);

        // Test that the controller has correct namespace and rest_base using reflection
        $reflection = new \ReflectionClass($controller);
        $namespaceProperty = $reflection->getProperty('namespace');
        $namespaceProperty->setAccessible(true);
        $restBaseProperty = $reflection->getProperty('rest_base');
        $restBaseProperty->setAccessible(true);

        $this->assertEquals(SubscriptionRoute::NAMESPACE, $namespaceProperty->getValue($controller));
        $this->assertEquals(SubscriptionRoute::BASE, $restBaseProperty->getValue($controller));
    }

    /**
     * @unreleased
     */
    public function testGetItemsPermissionCheck()
    {
        $response = $this->handleGetItemsRequest($this->subscription->id, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testGetItemsWithNonexistentSubscription()
    {
        $response = $this->handleGetItemsRequest(99999);

        $this->assertErrorResponse('subscription_not_found', $response, 404);
    }

    /**
     * @unreleased
     */
    public function testCreateItemPermissionCheck()
    {
        $noteData = [
            'content' => 'Test note',
            'type' => 'admin',
        ];

        $response = $this->handleCreateItemRequest($this->subscription->id, $noteData, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testCreateItemWithNonexistentSubscription()
    {
        $noteData = [
            'content' => 'Test note',
            'type' => 'admin',
        ];

        $response = $this->handleCreateItemRequest(99999, $noteData);

        $this->assertErrorResponse('subscription_not_found', $response, 404);
    }

    /**
     * @unreleased
     */
    public function testGetItemPermissionCheck()
    {
        $response = $this->handleGetItemRequest($this->subscription->id, 1, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testUpdateItemPermissionCheck()
    {
        $updateData = [
            'content' => 'Updated content',
        ];

        $response = $this->handleUpdateItemRequest($this->subscription->id, 1, $updateData, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testDeleteItemPermissionCheck()
    {
        $response = $this->handleDeleteItemRequest($this->subscription->id, 1, false);

        $this->assertErrorResponse('rest_forbidden', $response, 401);
    }

    /**
     * @unreleased
     */
    public function testControllerHasCorrectPermissionMethods()
    {
        $controller = new SubscriptionNotesController();

        $this->assertTrue(method_exists($controller, 'get_items_permissions_check'));
        $this->assertTrue(method_exists($controller, 'create_item_permissions_check'));
        $this->assertTrue(method_exists($controller, 'get_item_permissions_check'));
        $this->assertTrue(method_exists($controller, 'update_item_permissions_check'));
        $this->assertTrue(method_exists($controller, 'delete_item_permissions_check'));
    }

    /**
     * @unreleased
     */
    public function testGetItemsPermissionsCheckRequiresViewReportsCapability()
    {
        $request = new WP_REST_Request();

        // User without capability should be denied
        wp_set_current_user(0); // Anonymous user
        $result = $this->controller->get_items_permissions_check($request);
        $this->assertFalse($result);

        // User with capability should be allowed
        wp_set_current_user(self::$users['administrator']);
        $result = $this->controller->get_items_permissions_check($request);
        $this->assertTrue($result);
    }

    /**
     * @unreleased
     */
    public function testCreateItemPermissionsCheckRequiresEditPaymentsCapability()
    {
        $request = new WP_REST_Request();

        // User without capability should be denied
        wp_set_current_user(0); // Anonymous user
        $result = $this->controller->create_item_permissions_check($request);
        $this->assertFalse($result);

        // User with capability should be allowed
        wp_set_current_user(self::$users['administrator']);
        $result = $this->controller->create_item_permissions_check($request);
        $this->assertTrue($result);
    }

    /**
     * @unreleased
     */
    public function testGetItemPermissionsCheckRequiresViewReportsCapability()
    {
        $request = new WP_REST_Request();

        // User without capability should be denied
        wp_set_current_user(0); // Anonymous user
        $result = $this->controller->get_item_permissions_check($request);
        $this->assertFalse($result);

        // User with capability should be allowed
        wp_set_current_user(self::$users['administrator']);
        $result = $this->controller->get_item_permissions_check($request);
        $this->assertTrue($result);
    }

    /**
     * @unreleased
     */
    public function testUpdateItemPermissionsCheckRequiresEditPaymentsCapability()
    {
        $request = new WP_REST_Request();

        // User without capability should be denied
        wp_set_current_user(0); // Anonymous user
        $result = $this->controller->update_item_permissions_check($request);
        $this->assertFalse($result);

        // User with capability should be allowed
        wp_set_current_user(self::$users['administrator']);
        $result = $this->controller->update_item_permissions_check($request);
        $this->assertTrue($result);
    }

    /**
     * @unreleased
     */
    public function testDeleteItemPermissionsCheckRequiresEditPaymentsCapability()
    {
        $request = new WP_REST_Request();

        // User without capability should be denied
        wp_set_current_user(0); // Anonymous user
        $result = $this->controller->delete_item_permissions_check($request);
        $this->assertFalse($result);

        // User with capability should be allowed
        wp_set_current_user(self::$users['administrator']);
        $result = $this->controller->delete_item_permissions_check($request);
        $this->assertTrue($result);
    }

    /**
     * @unreleased
     */
    public function testGetCollectionParams()
    {
        $controller = new SubscriptionNotesController();
        $params = $controller->get_collection_params();

        $this->assertIsArray($params);
        $this->assertArrayHasKey('page', $params);
        $this->assertArrayHasKey('per_page', $params);
        $this->assertEquals(1, $params['page']['default']);
        $this->assertEquals(30, $params['per_page']['default']);

        // Verify removed parameters
        $this->assertArrayNotHasKey('context', $params);
        $this->assertArrayNotHasKey('search', $params);
    }

    /**
     * @unreleased
     */
    public function testGetItemSchema()
    {
        $controller = new SubscriptionNotesController();
        $schema = $controller->get_item_schema();

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('$schema', $schema);
        $this->assertArrayHasKey('title', $schema);
        $this->assertArrayHasKey('type', $schema);
        $this->assertArrayHasKey('properties', $schema);

        $properties = $schema['properties'];
        $this->assertArrayHasKey('id', $properties);
        $this->assertArrayHasKey('content', $properties);
        $this->assertArrayHasKey('subscriptionId', $properties);
        $this->assertArrayHasKey('type', $properties);
        $this->assertArrayHasKey('createdAt', $properties);

        // Verify property types
        $this->assertEquals('integer', $properties['id']['type']);
        $this->assertEquals('string', $properties['content']['type']);
        $this->assertEquals('integer', $properties['subscriptionId']['type']);
        $this->assertEquals('string', $properties['type']['type']);
        $this->assertEquals('string', $properties['createdAt']['type']);
    }

    /**
     * @unreleased
     */
    public function testGetPublicItemSchema()
    {
        $controller = new SubscriptionNotesController();
        $schema = $controller->get_public_item_schema();

        $this->assertIsArray($schema);
        $this->assertArrayHasKey('properties', $schema);
        $this->assertArrayHasKey('_links', $schema['properties']);
        $this->assertEquals('object', $schema['properties']['_links']['type']);
        $this->assertTrue($schema['properties']['_links']['readonly']);
    }

    /**
     * @unreleased
     */
    public function testGetEndpointArgsForItemSchemaCreatable()
    {
        $controller = new SubscriptionNotesController();
        $args = $controller->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE);

        $this->assertIsArray($args);
        $this->assertArrayHasKey('subscriptionId', $args);
        $this->assertArrayHasKey('content', $args);
        $this->assertArrayHasKey('type', $args);
        $this->assertArrayNotHasKey('id', $args);

        // Verify required fields for creation
        $this->assertTrue($args['content']['required']);
        $this->assertTrue($args['type']['required']);
    }

    /**
     * @unreleased
     */
    public function testGetEndpointArgsForItemSchemaEditable()
    {
        $controller = new SubscriptionNotesController();
        $args = $controller->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE);

        $this->assertIsArray($args);
        $this->assertArrayHasKey('subscriptionId', $args);
        $this->assertArrayHasKey('content', $args);
        $this->assertArrayHasKey('type', $args);
        $this->assertArrayHasKey('id', $args);

        // Verify fields are not required for updates
        $this->assertFalse($args['content']['required']);
        $this->assertFalse($args['type']['required']);
    }

    /**
     * @unreleased
     */
    public function testGetEndpointArgsForItemSchemaDeletable()
    {
        $controller = new SubscriptionNotesController();
        $args = $controller->get_endpoint_args_for_item_schema(WP_REST_Server::DELETABLE);

        $this->assertIsArray($args);
        $this->assertArrayHasKey('subscriptionId', $args);
        $this->assertArrayHasKey('id', $args);
        $this->assertArrayNotHasKey('content', $args);
        $this->assertArrayNotHasKey('type', $args);
    }

    /**
     * Helper method to handle GET items requests
     *
     * @unreleased
     */
    private function handleGetItemsRequest(
        int $subscriptionId,
        bool $authenticatedAsAdmin = true,
        array $queryParams = []
    ): WP_REST_Response {
        $request = $this->createRequest(
            'GET',
            '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscriptionId . '/notes',
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        if (!empty($queryParams)) {
            $request->set_query_params($queryParams);
        }

        return $this->dispatchRequest($request);
    }

    /**
     * Helper method to handle POST requests
     *
     * @unreleased
     */
    private function handleCreateItemRequest(
        int $subscriptionId,
        array $data,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'POST',
            '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscriptionId . '/notes',
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        $request->set_body_params($data);

        return $this->dispatchRequest($request);
    }

    /**
     * Helper method to handle GET single item requests
     *
     * @unreleased
     */
    private function handleGetItemRequest(
        int $subscriptionId,
        int $noteId,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'GET',
            '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscriptionId . '/notes/' . $noteId,
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        return $this->dispatchRequest($request);
    }

    /**
     * Helper method to handle PUT/PATCH requests
     *
     * @unreleased
     */
    private function handleUpdateItemRequest(
        int $subscriptionId,
        int $noteId,
        array $data,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'PUT',
            '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscriptionId . '/notes/' . $noteId,
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        $request->set_body_params($data);

        return $this->dispatchRequest($request);
    }

    /**
     * Helper method to handle DELETE requests
     *
     * @unreleased
     */
    private function handleDeleteItemRequest(
        int $subscriptionId,
        int $noteId,
        bool $authenticatedAsAdmin = true
    ): WP_REST_Response {
        $request = $this->createRequest(
            'DELETE',
            '/' . SubscriptionRoute::NAMESPACE . '/' . SubscriptionRoute::BASE . '/' . $subscriptionId . '/notes/' . $noteId,
            [],
            $authenticatedAsAdmin ? 'administrator' : 'anonymous'
        );

        return $this->dispatchRequest($request);
    }
}

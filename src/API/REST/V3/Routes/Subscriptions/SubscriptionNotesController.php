<?php

namespace Give\API\REST\V3\Routes\Subscriptions;

use Exception;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\Models\SubscriptionNote;
use Give\Subscriptions\ValueObjects\SubscriptionNoteType;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 4.8.0
 */
class SubscriptionNotesController extends WP_REST_Controller
{
    /**
     * @since 4.8.0
     */
    public function __construct()
    {
        $this->namespace = SubscriptionRoute::NAMESPACE;
        $this->rest_base = SubscriptionRoute::BASE;
    }

    /**
     * @since 4.9.0 Move schema key to the route level instead of defining it for each endpoint (which is incorrect)
     * @since 4.8.0
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<subscriptionId>[\d]+)/notes', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => array_merge([
                    'subscriptionId' => [
                        'description' => __('The ID of the subscription this note belongs to.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                ], $this->get_collection_params()),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<subscriptionId>[\d]+)/notes/(?P<id>[\d]+)',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_item'],
                    'permission_callback' => [$this, 'get_item_permissions_check'],
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::READABLE),
                ],
                [
                    'methods' => WP_REST_Server::EDITABLE,
                    'callback' => [$this, 'update_item'],
                    'permission_callback' => [$this, 'update_item_permissions_check'],
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::EDITABLE),
                ],
                [
                    'methods' => WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'delete_item'],
                    'permission_callback' => [$this, 'delete_item_permissions_check'],
                    'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::DELETABLE),
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
    }

    /**
     * Get a collection of subscription notes.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_items($request)
    {
        $subscription = Subscription::find($request->get_param('subscriptionId'));
        if (!$subscription) {
            return new WP_Error('subscription_not_found', __('Subscription not found', 'give'), ['status' => 404]);
        }

        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');

        $query = SubscriptionNote::query()
            ->where('comments.comment_post_ID', $subscription->id)
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy('createdAt', 'DESC');

        $notes = $query->getAll() ?? [];
        $notes = array_map(function ($note) use ($request) {
            $item = $this->prepare_item_for_response($note, $request);

            return $this->prepare_response_for_collection($item);
        }, $notes);

        $totalNotes = SubscriptionNote::query()->where('comments.comment_post_ID', $subscription->id)->count();
        $totalPages = (int)ceil($totalNotes / $perPage);

        $response = rest_ensure_response($notes);
        $response->header('X-WP-Total', $totalNotes);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            $request->get_query_params(),
            rest_url(sprintf('%s/%s/%d/notes', $this->namespace, $this->rest_base, $subscription->id))
        );

        if ($page > 1) {
            $prevPage = $page - 1;
            if ($prevPage > $totalPages) {
                $prevPage = $totalPages;
            }
            $response->link_header('prev', add_query_arg('page', $prevPage, $base));
        }

        if ($totalPages > $page) {
            $nextPage = $page + 1;
            $response->link_header('next', add_query_arg('page', $nextPage, $base));
        }

        return $response;
    }

    /**
     * Create a subscription note.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function create_item($request)
    {
        $subscription = Subscription::find($request->get_param('subscriptionId'));
        if (!$subscription) {
            return new WP_Error('subscription_not_found', __('Subscription not found', 'give'), ['status' => 404]);
        }

        $note = SubscriptionNote::create([
            'subscriptionId' => $subscription->id,
            'content' => $request->get_param('content'),
            'type' => new SubscriptionNoteType($request->get_param('type')),
        ]);

        $fieldsUpdate = $this->update_additional_fields_for_object($note, $request);

        if (is_wp_error($fieldsUpdate)) {
            return $fieldsUpdate;
        }

        $response = $this->prepare_item_for_response($note, $request);
        $response->set_status(201);

        return rest_ensure_response($response);
    }

    /**
     * Get a single subscription note.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item($request)
    {
        $subscription = Subscription::find($request->get_param('subscriptionId'));
        if (!$subscription) {
            return new WP_Error('subscription_not_found', __('Subscription not found', 'give'), ['status' => 404]);
        }

        $note = SubscriptionNote::find($request->get_param('id'));
        if (!$note || $note->subscriptionId !== $subscription->id) {
            return new WP_Error('note_not_found', __('Note not found', 'give'), ['status' => 404]);
        }

        $response = $this->prepare_item_for_response($note, $request);

        return rest_ensure_response($response);
    }

    /**
     * Update a subscription note.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function update_item($request)
    {
        $subscription = Subscription::find($request->get_param('subscriptionId'));
        if (!$subscription) {
            return new WP_Error('subscription_not_found', __('Subscription not found', 'give'), ['status' => 404]);
        }

        $note = SubscriptionNote::find($request->get_param('id'));
        if (!$note || $note->subscriptionId !== $subscription->id) {
            return new WP_Error('note_not_found', __('Note not found', 'give'), ['status' => 404]);
        }

        if ($request->has_param('content')) {
            $note->content = $request->get_param('content');
        }

        if ($request->has_param('type')) {
            $note->type = new SubscriptionNoteType($request->get_param('type'));
        }

        if ($note->isDirty()) {
            $note->save();
        }

        $fieldsUpdate = $this->update_additional_fields_for_object($note, $request);

        if (is_wp_error($fieldsUpdate)) {
            return $fieldsUpdate;
        }

        $response = $this->prepare_item_for_response($note, $request);

        return rest_ensure_response($response);
    }

    /**
     * Delete a subscription note.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function delete_item($request)
    {
        $subscription = Subscription::find($request->get_param('subscriptionId'));
        if (!$subscription) {
            return new WP_Error('subscription_not_found', __('Subscription not found', 'give'), ['status' => 404]);
        }

        $note = SubscriptionNote::find($request->get_param('id'));
        if (!$note || $note->subscriptionId !== $subscription->id) {
            return new WP_Error('note_not_found', __('Note not found', 'give'), ['status' => 404]);
        }

        // Store the note data before deletion for the response
        $noteData = $note->toArray();

        $note->delete();

        $response = new WP_REST_Response($noteData);

        //$response->set_status(200);

        return $response;
    }

    /**
     * @since 4.8.0
     */
    public function get_items_permissions_check($request): bool
    {
        return current_user_can('view_give_reports');
    }

    /**
     * @since 4.8.0
     */
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('edit_give_payments');
    }

    /**
     * @since 4.8.0
     */
    public function get_item_permissions_check($request): bool
    {
        return current_user_can('view_give_reports');
    }

    /**
     * @since 4.8.0
     */
    public function update_item_permissions_check($request): bool
    {
        return current_user_can('edit_give_payments');
    }

    /**
     * @since 4.8.0
     */
    public function delete_item_permissions_check($request): bool
    {
        return current_user_can('edit_give_payments');
    }

    /**
     * @since 4.8.0
     */
    public function prepare_item_for_response($note, $request): WP_REST_Response
    {
        $self_url = rest_url(
            sprintf(
                '%s/%s/%d/notes/%d',
                $this->namespace,
                $this->rest_base,
                $note->subscriptionId,
                $note->id
            )
        );

        $links = [
            'self' => ['href' => $self_url],
        ];

        $response = new WP_REST_Response($note->toArray());
        $response->add_links($links);
        $response->data = $this->add_additional_fields_to_object($response->data, $request);

        return $response;
    }

    /**
     * @since 4.8.0
     */
    public function get_collection_params(): array
    {
        $params = parent::get_collection_params();

        $params['page']['default'] = 1;
        $params['per_page']['default'] = 30;

        // Remove default parameters not being used
        unset($params['context']);
        unset($params['search']);

        return $params;
    }

    /**
     * Get the subscription note schema, conforming to JSON Schema.
     *
     * @since 4.8.0
     *
     * @return array
     */
    public function get_item_schema(): array
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/subscription-note',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'description' => __('Unique identifier for the note.', 'give'),
                    'type' => 'integer',
                    'readonly' => true,
                ],
                'content' => [
                    'description' => __('The content of the note.', 'give'),
                    'type' => 'string',
                    'required' => true,
                    'minLength' => 1,
                ],
                'subscriptionId' => [
                    'description' => __('The ID of the subscription this note belongs to.', 'give'),
                    'type' => 'integer',
                    'required' => true,
                ],
                'type' => [
                    'description' => __('The type of the note.', 'give'),
                    'type' => 'string',
                    'enum' => ['admin', 'subscription'],
                    'default' => 'admin',
                ],
                'createdAt' => [
                    'description' => __('The date the note was created.', 'give'),
                    'type' => 'string',
                    'format' => 'date-time',
                    'readonly' => true,
                ],
            ],
        ];

        return $this->add_additional_fields_schema($schema);
    }

    /**
     * Get the subscription note schema for public display.
     *
     * @since 4.8.0
     *
     * @return array
     */
    public function get_public_item_schema(): array
    {
        $schema = $this->get_item_schema();

        // Add additional properties for public display
        $schema['properties']['_links'] = [
            'description' => __('HATEOAS links for the subscription note.', 'give'),
            'type' => 'object',
            'readonly' => true,
        ];

        return $schema;
    }

    /**
     * @since 4.8.0
     */
    public function get_endpoint_args_for_item_schema($method = WP_REST_Server::CREATABLE): array
    {
        $args = parent::get_endpoint_args_for_item_schema($method);
        $schema = $this->get_item_schema();

        // Common argument for all endpoints
        $args['subscriptionId'] = $schema['properties']['subscriptionId'];
        $args['subscriptionId']['in'] = 'path';

        // Arguments for single item endpoints (not for POST)
        if (in_array($method, [WP_REST_Server::READABLE, WP_REST_Server::EDITABLE, WP_REST_Server::DELETABLE], true)) {
            $args['id'] = [
                'description' => __('The note ID.', 'give'),
                'type' => 'integer',
                'required' => true,
                'in' => 'path',
            ];
        } else {
            // Remove id if present (for POST)
            unset($args['id']);
        }

        // Arguments for create/update endpoints
        if (in_array($method, [WP_REST_Server::CREATABLE, WP_REST_Server::EDITABLE], true)) {
            $args['content'] = [
                'description' => __('The content of the note.', 'give'),
                'type' => 'string',
                'required' => $method === WP_REST_Server::CREATABLE,
                'minLength' => 1,
            ];

            $args['type'] = [
                'description' => __('The type of the note.', 'give'),
                'type' => 'string',
                'required' => $method === WP_REST_Server::CREATABLE,
                'enum' => ['admin', 'subscription'],
                'default' => 'admin',
            ];
        } else {
            unset($args['content']);
            unset($args['type']);
        }

        return $args;
    }
}

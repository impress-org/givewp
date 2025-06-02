<?php

namespace Give\API\REST\V3\Routes\Donors;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\Models\Donor;
use Give\Donors\Models\DonorNote;
use Give\Donors\ValueObjects\DonorNoteType;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class DonorNotesController extends WP_REST_Controller
{
    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = DonorRoute::NAMESPACE;
        $this->rest_base = DonorRoute::BASE;
    }

    /**
     * @unreleased
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<donorId>[\d]+)/notes', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => $this->get_collection_params(),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => $this->get_endpoint_args_for_item_schema(WP_REST_Server::CREATABLE),
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<donorId>[\d]+)/notes/(?P<id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => [
                    'donorId' => [
                        'description' => __('The donor ID.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'id' => [
                        'description' => __('The note ID.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
                'args' => [
                    'donorId' => [
                        'description' => __('The donor ID.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'id' => [
                        'description' => __('The note ID.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get a collection of donor notes.
     *
     * @unreleased
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_items($request)
    {
        $donor = Donor::find($request->get_param('donorId'));
        if (!$donor) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');

        $query = DonorNote::query()
            ->where('comment_parent', $donor->id)
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy('createdAt', 'DESC');

        $notes = $query->getAll() ?? [];
        $notes = array_map(function ($note) use ($request) {
            $item = $this->prepare_item_for_response($note, $request);
            return $this->prepare_response_for_collection($item);
        }, $notes);

        $totalNotes = DonorNote::query()->where('comment_parent', $donor->id)->count();
        $totalPages = (int)ceil($totalNotes / $perPage);

        $response = rest_ensure_response($notes);
        $response->header('X-WP-Total', $totalNotes);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            $request->get_query_params(),
            rest_url(sprintf('%s/%s/%d/notes', $this->namespace, $this->rest_base, $donor->id))
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
     * Get a single donor note.
     *
     * @unreleased
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item($request)
    {
        $donor = Donor::find($request->get_param('donorId'));
        if (!$donor) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $note = DonorNote::find($request->get_param('id'));
        if (!$note || $note->donorId !== $donor->id) {
            return new WP_Error('note_not_found', __('Note not found', 'give'), ['status' => 404]);
        }

        $response = $this->prepare_item_for_response($note, $request);
        return rest_ensure_response($response);
    }

    /**
     * Create a donor note.
     *
     * @unreleased
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function create_item($request)
    {
        $donor = Donor::find($request->get_param('donorId'));
        if (!$donor) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $note = DonorNote::create([
            'donorId' => $donor->id,
            'content' => $request->get_param('content'),
            'type' => new DonorNoteType($request->get_param('type')),
        ]);

        $response = $this->prepare_item_for_response($note, $request);
        $response->set_status(201);

        return rest_ensure_response($response);
    }

    /**
     * Delete a donor note.
     *
     * @unreleased
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function delete_item($request)
    {
        $donor = Donor::find($request->get_param('donorId'));
        if (!$donor) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $note = DonorNote::find($request->get_param('id'));
        if (!$note || $note->donorId !== $donor->id) {
            return new WP_Error('note_not_found', __('Note not found', 'give'), ['status' => 404]);
        }

        $note->delete();

        return new WP_REST_Response(null, 204);
    }

    /**
     * @unreleased
     */
    public function get_items_permissions_check($request): bool
    {
        return current_user_can('view_give_reports');
    }

    /**
     * @unreleased
     */
    public function get_item_permissions_check($request): bool
    {
        return current_user_can('view_give_reports');
    }

    /**
     * @unreleased
     */
    public function create_item_permissions_check($request): bool
    {
        return current_user_can('edit_give_payments');
    }

    /**
     * @unreleased
     */
    public function delete_item_permissions_check($request): bool
    {
        return current_user_can('edit_give_payments');
    }

    /**
     * @unreleased
     */
    public function prepare_item_for_response($note, $request): WP_REST_Response
    {
        $self_url = rest_url(sprintf(
            '%s/%s/%d/notes/%d',
            $this->namespace,
            $this->rest_base,
            $note->donorId,
            $note->id
        ));

        $links = [
            'self' => ['href' => $self_url],
        ];

        $response = new WP_REST_Response($note->toArray());
        $response->add_links($links);

        return $response;
    }

    /**
     * @unreleased
     */
    public function get_collection_params(): array
    {
        $params = parent::get_collection_params();

        $params['page']['default'] = 1;
        $params['per_page']['default'] = 30;

        return $params;
    }

    /**
     * @unreleased
     */
    public function get_endpoint_args_for_item_schema($method = WP_REST_Server::CREATABLE): array
    {
        $args = [];

        if (WP_REST_Server::CREATABLE === $method) {
            $args['content'] = [
                'description' => __('The content of the note.', 'give'),
                'type' => 'string',
                'required' => true,
            ];

            $args['type'] = [
                'description' => __('The type of the note.', 'give'),
                'type' => 'string',
                'default' => 'admin',
                'enum' => ['admin', 'donor'],
            ];
        }

        return $args;
    }
}

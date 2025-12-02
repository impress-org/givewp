<?php

namespace Give\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donors\Actions\GetDonorCollectionParams;
use Give\API\REST\V3\Routes\Donors\Actions\GetDonorItemSchema;
use Give\API\REST\V3\Routes\Donors\Actions\GetDonorSharedParamsForGetMethods;
use Give\API\REST\V3\Routes\Donors\Permissions\DonorPermissions;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\API\REST\V3\Support\CURIE;
use Give\API\REST\V3\Support\Item;
use Give\Donors\DonorsQuery;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorAddress;
use Give\Donors\ViewModels\DonorViewModel;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * The methods using snake case like register_routes() are present in the base class,
 * and the methods using camel case like getSortColumn() are available only on this class.
 *
 * @unreleased Extract permissions, collection params, and shared params to separate classes
 * @since 4.4.0 Extends WP_REST_Controller class and rename methods
 * @since 4.0.0
 */
class DonorController extends WP_REST_Controller
{
    /**
     * @since 4.0.0
     */
    public function __construct()
    {
        $this->namespace = DonorRoute::NAMESPACE;
        $this->rest_base = DonorRoute::BASE;
    }

    /**
     * @since 4.9.0 Move schema key to the route level instead of defining it for each endpoint (which is incorrect)
     * @since 4.0.0
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => array_merge($this->get_collection_params(), give(GetDonorSharedParamsForGetMethods::class)()),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => array_merge([
                    'id' => [
                        'description' => __(
                            'The donor ID.',
                            'give'
                        ),
                        'type' => 'integer',
                        'required' => true,
                    ],
                    '_embed' => [
                        'description' => __(
                            'Whether to embed related resources in the response. It can be true when we want to embed all available resources, or a string like "givewp:statistics" when we wish to embed only a specific one.',
                            'give'
                        ),
                        'type' => ['string', 'boolean'],
                        'default' => false,
                    ],
                    'mode' => [
                        'description' => __(
                            'The mode of donations to filter by "live" or "test" (it only gets applied when "_embed" is set).',
                            'give'
                        ),
                        'type' => 'string',
                        'default' => 'live',
                        'enum' => ['live', 'test'],
                    ],
                    'campaignId' => [
                        'description' => __(
                            'The ID of the campaign to filter donors by - zero or empty mean "all campaigns" (it only gets applied when "_embed" is set).',
                            'give'
                        ),
                        'type' => 'integer',
                        'default' => 0,
                    ],
                ], give(GetDonorSharedParamsForGetMethods::class)()),
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
                'args' => rest_get_endpoint_args_for_schema($this->get_public_item_schema(), WP_REST_Server::EDITABLE),
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * Get list of donors.
     *
     * @since 4.8.0 Add support for search parameter
     * @since 4.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_items($request)
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $sortColumn = $this->getSortColumn($request->get_param('sort'));
        $sortDirection = $request->get_param('direction');
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));

        $query = new DonorsQuery();

        if ($request->get_param('search')) {
            $query->whereLike('name', '%%' . $request->get_param('search') . '%%');
            $query->orWhereLike('email', '%%' . $request->get_param('search') . '%%');
        }

        // Donors only can be donors if they have donations associated with them
        if ($request->get_param('onlyWithDonations')) {
            $mode = $request->get_param('mode');
            $campaignId = $request->get_param('campaignId');
            $query->whereDonorsHaveDonations($mode, $campaignId, $donorAnonymousMode->isExcluded());
        }

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy($sortColumn, $sortDirection);

        $donors = $query->getAll() ?? [];
        $donors = array_map(function ($donor) use ($includeSensitiveData, $donorAnonymousMode, $request) {
            $item = (new DonorViewModel($donor))->anonymousMode($donorAnonymousMode)->includeSensitiveData($includeSensitiveData)->exports();
            $response = $this->prepare_item_for_response($item, $request);

            return $this->prepare_response_for_collection($response);
        }, $donors);

        $totalDonors = empty($donors) ? 0 : Donor::query()->count();
        $totalPages = (int)ceil($totalDonors / $perPage);

        $response = rest_ensure_response($donors);
        $response->header('X-WP-Total', $totalDonors);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url(DonorRoute::BASE)
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
     * Get a single donor.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item($request)
    {
        $donor = Donor::find($request->get_param('id'));
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));

        if (!$donor || ($donor->isAnonymous() && $donorAnonymousMode->isExcluded())) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $item = (new DonorViewModel($donor))->anonymousMode($donorAnonymousMode)->includeSensitiveData($includeSensitiveData)->exports();
        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Update a single donor.
     *
     * @since 4.8.0 Update donor name when firstName or lastName is updated
     * @since 4.7.0 Add support for updating custom fields
     * @since 4.4.0
     *
     * @return WP_REST_Response|WP_Error
     */
    public function update_item($request)
    {
        $donor = Donor::find($request->get_param('id'));

        if (!$donor) {
            return new WP_REST_Response(__('Donor not found', 'give'), 404);
        }

        $nonEditableFields = [
            'id',
            'userId',
            'createdAt',
        ];

        foreach ($request->get_params() as $key => $value) {
            if (!in_array($key, $nonEditableFields)) {
                if ($donor->hasProperty($key)) {
                    if ($key === 'addresses') {
                        $donor->addresses = array_map(function ($address) {
                            return DonorAddress::fromArray($address);
                        }, $value);
                        continue;
                    }

                    if (!$donor->isPropertyTypeValid($key, $value)) {
                        $value = null;
                    }

                    $donor->$key = $value;
                }
            }
        }

        if ($request->get_param('firstName') || $request->get_param('lastName')) {
            $donor->name = trim($donor->firstName . ' ' . $donor->lastName);
        }

        if ($donor->isDirty()) {
            $donor->save();
        }

        $item = (new DonorViewModel($donor))->includeSensitiveData(true)->anonymousMode(DonorAnonymousMode::INCLUDED())->exports();
        $fieldsUpdate = $this->update_additional_fields_for_object($item, $request);

        if (is_wp_error($fieldsUpdate)) {
            return $fieldsUpdate;
        }

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * @unreleased Use DonorPermissions class
     * @since 4.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function get_items_permissions_check($request)
    {
        return DonorPermissions::validationForGetMethods($request);
    }

    /**
     * @unreleased Use DonorPermissions class
     * @since 4.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function get_item_permissions_check($request)
    {
        return DonorPermissions::validationForGetMethods($request);
    }

    /**
     * @unreleased Use DonorPermissions class
     * @since 4.4.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function update_item_permissions_check($request)
    {
        return DonorPermissions::validationForUpdateMethod($request);
    }

    /**
     * @unreleased Format dates as strings using Item::formatDatesForResponse
     * @since 4.7.0 Add support for adding custom fields to the response
     * @since 4.4.0
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $request->get_param('id')));
        $statistics_url = add_query_arg([
            'mode' => $request->get_param('mode'),
            'campaignId' => $request->get_param('campaignId'),
        ], $self_url . '/statistics');
        $links = [
            'self' => ['href' => $self_url],
            CURIE::relationUrl('statistics') => [
                'href' => $statistics_url,
                'embeddable' => true,
            ],
        ];

        $response = new WP_REST_Response(Item::formatDatesForResponse($item, ['createdAt']));
        $response->add_links($links);
        $response->data = $this->add_additional_fields_to_object($response->data, $request);

        return $response;
    }

    /**
     * @unreleased Add missing properties to the schema and extract it to GetDonorItemSchema class
     * @since 4.13.0 add schema description
     * @since 4.9.0 Set proper JSON Schema version
     * @since 4.7.0 Change title to givewp/donor and add custom fields schema
     * @since 4.4.0
     */
    public function get_item_schema(): array
    {
        $schema = give(GetDonorItemSchema::class)();
        return $this->add_additional_fields_schema($schema);
    }

    /**
     * @unreleased Extract collection params to GetDonorCollectionParams class
     * @since 4.8.0 Re-add search parameter
     * @since 4.4.0
     */
    public function get_collection_params(): array
    {
        $params = parent::get_collection_params();

        $params['page']['default'] = 1;
        $params['per_page']['default'] = 30;

        // Remove default parameters not being used
        unset($params['context']);

        $params += give(GetDonorCollectionParams::class)();

        return $params;
    }

    /**
     * @since 4.13.1 cast totalAmountDonated to decimal
     * @since 4.0.0
     */
    public function getSortColumn(string $sortColumn): string
    {
        $sortColumnsMap = [
            'id' => 'id',
            'createdAt' => 'date_created',
            'name' => 'name',
            'firstName' => 'give_donormeta_attach_meta_firstName.meta_value',
            'lastName' => 'give_donormeta_attach_meta_lastName.meta_value',
            'totalAmountDonated' => 'CAST(purchase_value AS DECIMAL(10, 2))',
            'totalNumberOfDonations' => 'purchase_count',
        ];

        return $sortColumnsMap[$sortColumn];
    }
}

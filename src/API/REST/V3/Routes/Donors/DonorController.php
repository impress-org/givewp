<?php

namespace Give\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\API\REST\V3\Routes\Donors\Permissions\DonorPermissions;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\API\REST\V3\Routes\Donors\ViewModels\DonorViewModel;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\API\REST\V3\Support\CURIE;
use Give\API\REST\V3\Support\Headers;
use Give\API\REST\V3\Support\Item;
use Give\Donors\DonorsQuery;
use Give\Donors\Models\Donor;
use Give\Donors\ValueObjects\DonorAddress;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * The methods using snake case like register_routes() are present in the base class,
 * and the methods using camel case like getSortColumn() are available only on this class.
 *
 * @since 4.14.0 Extract permissions logic to separate classes
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
                'args' => array_merge($this->get_collection_params(), $this->getSharedParamsForGetMethods()),
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
                            'Whether to embed related resources in the response. It can be true when we want to embed all available resources, or a string like "givewp:statistics" when we wish to embed only a specific one. Available embeddable resources: givewp:statistics | givewp:donations | givewp:subscriptions. IMPORTANT: Use with caution when setting to true, as donations and subscriptions return 30 items by default, which can result in a large payload.',
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
                ], $this->getSharedParamsForGetMethods()),
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
     * @since 4.14.0 Use Headers::addPagination() helper for pagination headers
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

        $totalQuery = $query->clone();
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

        $totalDonors = empty($donors) ? 0 : $totalQuery->count();
        $response = rest_ensure_response($donors);
        $response = Headers::addPagination($response, $request, $totalDonors, $perPage, $this->rest_base);

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
     * @since 4.14.0 Use DonorPermissions class
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
     * @since 4.14.0 Use DonorPermissions class
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
     * @since 4.14.0 Use DonorPermissions class
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
     * @since 4.14.0 Add links to donations and subscriptions and format dates as strings using Item::formatDatesForResponse
     * @since 4.7.0 Add support for adding custom fields to the response
     * @since 4.4.0
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $donorId = $request->get_param('id');
        $mode = $request->get_param('mode');
        $campaignId = $request->get_param('campaignId');
        $includeSensitiveData = $request->get_param('includeSensitiveData') ? '1' : '0';
        $anonymousDonors = $request->get_param('anonymousDonors');
        $anonymousDonations = $anonymousDonors;

        $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $donorId));

        $statistics_url = add_query_arg([
            'mode' => $mode,
            'campaignId' => $campaignId,
        ], $self_url . '/statistics');

        $donations_url = rest_url(sprintf('%s/%s', DonationRoute::NAMESPACE, DonationRoute::BASE));
        $donations_url = add_query_arg([
            'donorId' => $donorId,
            'mode' => $mode,
            'campaignId' => $campaignId,
            'includeSensitiveData' => $includeSensitiveData,
            'anonymousDonations' => $anonymousDonations,
            'page' => 1,
            'per_page' => 30,
        ], $donations_url);

        $subscriptions_url = rest_url(sprintf('%s/%s', SubscriptionRoute::NAMESPACE, SubscriptionRoute::BASE));
        $subscriptions_url = add_query_arg([
            'donorId' => $donorId,
            'mode' => $mode,
            'campaignId' => $campaignId,
            'includeSensitiveData' => $includeSensitiveData,
            'anonymousDonors' => $anonymousDonors,
            'page' => 1,
            'per_page' => 30,
        ], $subscriptions_url);

        $links = [
            'self' => ['href' => $self_url],
            CURIE::relationUrl('statistics') => [
                'href' => $statistics_url,
                'embeddable' => true,
            ],
            CURIE::relationUrl('donations') => [
                'href' => $donations_url,
                'embeddable' => true,
            ],
            CURIE::relationUrl('subscriptions') => [
                'href' => $subscriptions_url,
                'embeddable' => true,
            ],
        ];

        $response = new WP_REST_Response(Item::formatDatesForResponse($item, ['createdAt']));
        $response->add_links($links);
        $response->data = $this->add_additional_fields_to_object($response->data, $request);

        return $response;
    }

    /**
     * @since 4.14.0 Add missing properties to the schema
     * @since 4.13.0 add schema description
     * @since 4.9.0 Set proper JSON Schema version
     * @since 4.7.0 Change title to givewp/donor and add custom fields schema
     * @since 4.4.0
     */
    public function get_item_schema(): array
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/donor',
            'description' => esc_html__('Donor routes for CRUD operations', 'give'),
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donor ID', 'give'),
                    'readonly' => true,
                ],
                'prefix' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor prefix', 'give'),
                    'format' => 'text-field',
                ],
                'firstName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor first name', 'give'),
                    'minLength' => 1,
                    'maxLength' => 128,
                    'errorMessage' => esc_html__('First name is required', 'give'),
                    'format' => 'text-field',
                    'required' => true,
                ],
                'lastName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor last name', 'give'),
                    'minLength' => 1,
                    'maxLength' => 128,
                    'errorMessage' => esc_html__('Last name is required', 'give'),
                    'format' => 'text-field',
                    'required' => true,
                ],
                'email' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor email', 'give'),
                    'format' => 'email',
                    'required' => true,
                ],
                'additionalEmails' => [
                    'type' => 'array',
                    'description' => esc_html__('Donor additional emails', 'give'),
                    'items' => [
                        'type' => 'string',
                        'format' => 'email',
                    ],
                ],
                'phone' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor phone', 'give'),
                    'pattern' => '^$|^[\+]?[1-9][\d\s\-\(\)]{7,20}$',
                ],
                'company' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor company', 'give'),
                    'format' => 'text-field',
                ],
                'avatarId' => [
                    'type' => ['integer', 'string', 'null'],
                    'description' => esc_html__('Donor avatar ID', 'give'),
                    'pattern' => '^$|^[0-9]+$',
                    'errorMessage' => esc_html__('Invalid avatar ID', 'give'),
                ],
                'addresses' => [
                    'type' => 'array',
                    'description' => esc_html__('Donor addresses', 'give'),
                    'items' => [
                        'type' => 'object',
                        'description' => esc_html__('Donor address', 'give'),
                        'properties' => [
                            'address1' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address line 1', 'give'),
                                'format' => 'text-field',
                            ],
                            'address2' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address line 2', 'give'),
                                'format' => 'text-field',
                            ],
                            'city' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address city', 'give'),
                                'format' => 'text-field',
                            ],
                            'state' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address state', 'give'),
                                'format' => 'text-field',
                            ],
                            'country' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address country', 'give'),
                                'format' => 'text-field',
                            ],
                            'zip' => [
                                'type' => 'string',
                                'description' => esc_html__('Donor address zip', 'give'),
                                'format' => 'text-field',
                            ],
                        ],
                    ],
                ],
                'customFields' => [
                    'type' => 'array',
                    'readonly' => true,
                    'description' => esc_html__('Custom fields (sensitive data)', 'give'),
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'label' => [
                                'type' => 'string',
                                'description' => esc_html__('Field label', 'give'),
                                'format' => 'text-field',
                            ],
                            'value' => [
                                'type' => 'string',
                                'description' => esc_html__('Field value', 'give'),
                                'format' => 'text-field',
                            ],
                        ],
                    ],
                ],
                'createdAt' => [
                    'type' => ['string', 'null'],
                    'description' => sprintf(
                        /* translators: %s: WordPress documentation URL */
                        esc_html__('Donor creation date in ISO 8601 format. Follows WordPress REST API date format standards. See %s for more information.', 'give'),
                        '<a href="https://developer.wordpress.org/rest-api/extending-the-rest-api/schema/#format" target="_blank">WordPress REST API Date and Time</a>'
                    ),
                    'format' => 'date-time',
                    'example' => '2025-09-02T20:27:02',
                    'readonly' => true,
                ],
                'userId' => [
                    'type' => ['integer', 'null'],
                    'description' => esc_html__('WordPress user ID associated with the donor', 'give'),
                    'readonly' => true,
                ],
                'name' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor full name (calculated from firstName and lastName)', 'give'),
                    'readonly' => true,
                ],
                'avatarUrl' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('URL of the donor avatar image', 'give'),
                    'format' => 'uri',
                    'readonly' => true,
                ],
                'wpUserPermalink' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Link to edit the WordPress user associated with the donor', 'give'),
                    'format' => 'uri',
                    'readonly' => true,
                ],
                'totalAmountDonated' => [
                    'type' => 'object',
                    'properties' => [
                        'value' => [
                            'type' => 'number',
                            'description' => esc_html__('Total amount donated in decimal format', 'give'),
                        ],
                        'valueInMinorUnits' => [
                            'type' => 'integer',
                            'description' => esc_html__('Total amount donated in minor units (cents)', 'give'),
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                            'description' => esc_html__('Currency code (e.g., USD, EUR)', 'give'),
                        ],
                    ],
                    'description' => esc_html__('Total amount donated by the donor', 'give'),
                    'readonly' => true,
                ],
                'totalNumberOfDonations' => [
                    'type' => 'integer',
                    'description' => esc_html__('Total number of donations made by the donor', 'give'),
                    'readonly' => true,
                ],
            ],
        ];

        return $this->add_additional_fields_schema($schema);
    }

    /**
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

        $params += [
            'sort' => [
                'description' => __('The field by which to sort the donors.', 'give'),
                'type' => 'string',
                'default' => 'id',
                'enum' => [
                    'id',
                    'createdAt',
                    'name',
                    'firstName',
                    'lastName',
                    'totalAmountDonated',
                    'totalNumberOfDonations',
                ],
            ],
            'direction' => [
                'description' => __('The direction of sorting: ascending (ASC) or descending (DESC).', 'give'),
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
            ],
            'onlyWithDonations' => [
                'description' => __('Whether to include only donors who have made donations.', 'give'),
                'type' => 'boolean',
                'default' => true,
            ],
            'mode' => [
                'description' => __(
                    'The mode of donations to filter by "live" or "test" (it only gets applied when "onlyWithDonations" is set to true).',
                    'give'
                ),
                'type' => 'string',
                'default' => 'live',
                'enum' => ['live', 'test'],
            ],
            'campaignId' => [
                'description' => __(
                    'The ID of the campaign to filter donors by - zero or empty mean "all campaigns" (it only gets applied when "onlyWithDonations" is set to true).',
                    'give'
                ),
                'type' => 'integer',
                'default' => 0,
            ],
            'search' => [
                'description' => __('Search donors by name or email.', 'give'),
                'type' => 'string',
            ],
        ];

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

    /**
     * Get shared parameters for GET methods (both collection and item).
     *
     * @since 4.4.0
     *
     * @return array
     */
    private function getSharedParamsForGetMethods(): array
    {
        return [
            'includeSensitiveData' => [
                'description' => __(
                    'Include or not include data that can be used to contact or locate the donors, such as phone number, email, billing address, etc. (require proper permissions)',
                    'give'
                ),
                'type' => 'boolean',
                'default' => false,
            ],
            'anonymousDonors' => [
                'description' => __(
                    'Exclude, include, or redact data that can be used to identify the donors, such as ID, first name, last name, etc (require proper permissions).',
                    'give'
                ),
                'type' => 'string',
                'default' => 'exclude',
                'enum' => ['exclude', 'include', 'redact'],
            ],
        ];
    }
}

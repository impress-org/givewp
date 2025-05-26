<?php

namespace Give\API\REST\V3\Routes\Donors;

use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorRoute;
use Give\Donors\DonorsQuery;
use Give\Donors\Models\Donor;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * The methods using snake case like register_routes() are present in the base class,
 * and the methods using camel case like escDonor() are available only on this class.
 *
 * @unreleased Extends WP_REST_Controller class and rename methods
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
     * @since 4.0.0
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'permissionsCheck'],
                'args' => $this->get_collection_params(),
            ],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'permissionsCheck'],
                'args' => $this->get_collection_params(),
            ],
        ]);
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

        if ( ! $donor || ($this->isAnonymousDonor($donor) && $donorAnonymousMode->isExcluded())) {
            return new WP_Error('donor_not_found', __('Donor not found', 'give'), ['status' => 404]);
        }

        $item = $this->escDonor($donor, $includeSensitiveData, $donorAnonymousMode);
        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Get list of donors.
     *
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
            $item = $this->escDonor($donor, $includeSensitiveData, $donorAnonymousMode);
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
     * @unreleased
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $request->get_param('id')));
        $links = [
            'self' => ['href' => $self_url],
            'statistics' => [
                'href' => $self_url . '/statistics',
                'embeddable' => true,
            ],
        ];

        $response = new WP_REST_Response($item);
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
                'description' => __('The mode of donations to filter by "live" or "test" (it only gets applied when "onlyWithDonations" is set to true).',
                    'give'),
                'type' => 'string',
                'default' => 'live',
                'enum' => ['live', 'test'],
            ],
            'campaignId' => [
                'description' => __('The ID of the campaign to filter donors by (it only gets applied when "onlyWithDonations" is set to true). Zero or empty values will be considered as "all campaigns".',
                    'give'),
                'type' => 'integer',
                'default' => 0,
            ],
            'includeSensitiveData' => [
                'description' => __('Include or not include data that can be used to contact or locate the donors, such as phone number, email, billing address, etc. (require proper permissions)',
                    'give'),
                'type' => 'boolean',
                'default' => false,
            ],
            'anonymousDonors' => [
                'description' => __('Exclude, include, or redact data that can be used to identify the donors, such as ID, first name, last name, etc (require proper permissions).',
                    'give'),
                'type' => 'string',
                'default' => 'exclude',
                'enum' => ['exclude', 'include', 'redact'],
            ],
            '_embed' => [
                'description' => __('Whether to embed related resources in the response. It can be true when we want to embed all available resources, or a string like "statistics" when we wish to embed only a specific one (applied only when retrieving a single donor).',
                    'give'),
                'type' => ['string', 'boolean'],
                'default' => false,
            ],
        ];

        return $params;
    }

    /**
     * @since 4.0.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function permissionsCheck(WP_REST_Request $request)
    {
        $isAdmin = current_user_can('manage_options');

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        if ( ! $isAdmin && $includeSensitiveData) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include sensitive data.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));
        if ( ! $isAdmin && $donorAnonymousMode->isIncluded()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include anonymous donors.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * @since 4.0.0
     */
    public function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * @since 4.0.0
     */
    public function escDonor(
        Donor $donor,
        bool $includeSensitiveData = false,
        DonorAnonymousMode $donationAnonymousMode = null
    ): array {
        $sensitiveDataExcluded = [];
        if ( ! $includeSensitiveData) {
            $sensitiveDataExcluded = [
                'userId',
                'email',
                'phone',
                'additionalEmails',
            ];
        }

        $anonymousDataRedacted = [];
        if ( ! is_null($donationAnonymousMode) && $donationAnonymousMode->isRedacted() && $this->isAnonymousDonor($donor)) {
            $anonymousDataRedacted = [
                'id',
                'name',
                'firstName',
                'lastName',
                'prefix',
            ];
        }

        $donor = $donor->toArray();

        foreach ($sensitiveDataExcluded as $property) {
            if (array_key_exists($property, $donor)) {
                unset($donor[$property]);
            }
        }

        foreach ($anonymousDataRedacted as $property) {
            if (array_key_exists($property, $donor)) {
                $donor[$property] = 'id' === $property ? 0 : __('anonymous', 'give');
            }
        }

        return $donor;
    }

    /**
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
            'totalAmountDonated' => 'purchase_value',
            'totalNumberOfDonations' => 'purchase_count',
        ];

        return $sortColumnsMap[$sortColumn];
    }

    /**
     * @since 4.0.0
     */
    private function isAnonymousDonor(Donor $donor): bool
    {
        $isAnonymousDonor = false;

        if ($donor->donations) {
            foreach ($donor->donations as $donation) {
                if ($donation->anonymous) {
                    $isAnonymousDonor = true;
                    break;
                }
            }
        }

        return $isAnonymousDonor;
    }
}

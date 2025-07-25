<?php

namespace Give\API\REST\V3\Routes\Subscriptions;

use Give\API\REST\V3\Routes\CURIE;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\API\REST\V3\Routes\Subscriptions\Actions\GetSubscriptionCollectionParams;
use Give\API\REST\V3\Routes\Subscriptions\Actions\GetSubscriptionItemSchema;
use Give\API\REST\V3\Routes\Subscriptions\Actions\GetSubscriptionSharedParamsForGetMethods;
use Give\API\REST\V3\Routes\Subscriptions\Helpers\SubscriptionFields;
use Give\API\REST\V3\Routes\Subscriptions\Helpers\SubscriptionPermissions;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\SubscriptionQuery;
use Give\Subscriptions\ViewModels\SubscriptionViewModel;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class SubscriptionController extends WP_REST_Controller
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $rest_base;

    /**
     * @unreleased
     */
    public function __construct()
    {
        $this->namespace = SubscriptionRoute::NAMESPACE;
        $this->rest_base = SubscriptionRoute::BASE;
    }

    /**
     * @unreleased
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/'.$this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => array_merge($this->get_collection_params(), give(GetSubscriptionSharedParamsForGetMethods::class)()),
                'schema' => [$this, 'get_public_item_schema'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'deleteItems'],
                'permission_callback' => [$this, 'delete_items_permissions_check'],
                'args' => [
                    'ids' => [
                        'description' => __('Array of subscription IDs to delete', 'give'),
                        'type' => 'array',
                        'items' => [
                            'type' => 'integer',
                        ],
                        'required' => true,
                    ],
                    'force' => [
                        'description' => __('Whether to permanently delete (force=true) or move to trash (force=false, default).', 'give'),
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ],
        ]);

        register_rest_route($this->namespace, '/'.$this->rest_base.'/(?P<id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
                'args' => array_merge([
                    'id' => [
                        'description' => __('The subscription ID.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                    '_embed' => [
                        'description' => __(
                            'Whether to embed related resources in the response. It can be true when we want to embed all available resources, or a string like "givewp:donation" when we wish to embed only a specific one.',
                            'give'
                        ),
                        'type' => [
                            'string',
                            'boolean',
                        ],
                        'default' => false,
                    ],
                ], give(GetSubscriptionSharedParamsForGetMethods::class)()),
                'schema' => [$this, 'get_public_item_schema'],
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
                'args' => rest_get_endpoint_args_for_schema($this->get_item_schema(), WP_REST_Server::EDITABLE),
                'schema' => [$this, 'get_public_item_schema'],
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
                'args' => [
                    'id' => [
                        'description' => __('The subscription ID.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'force' => [
                        'description' => __('Whether to permanently delete (force=true) or move to trash (force=false, default).', 'give'),
                        'type' => 'boolean',
                        'default' => false,
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ],
        ]);
    }

    /**
     * @unreleased
     */
    public function get_items($request)
    {
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $sortColumn = $this->getSortColumn($request->get_param('sort'));
        $sortDirection = $request->get_param('direction');
        $mode = $request->get_param('mode');
        $status = $request->get_param('status');
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));

        $query = new SubscriptionQuery();
        $query->whereMode($mode);

        if ($campaignId = $request->get_param('campaignId')) {
            $query->whereCampaignId($campaignId);
        }

        if ($donorAnonymousMode->isExcluded()) {
            $query->excludeAnonymousDonors();
        }

        if ($donorId = $request->get_param('donorId')) {
            $query->whereDonorId($donorId);
        }

        if (! in_array('any', (array) $status, true)) {
            $query->whereStatus((array)$status);
        }

        $totalQuery = $query->clone();

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy($sortColumn, $sortDirection);

        $sql = $query->getSQL();

        $subscriptions = $query->getAll() ?? [];

        $subscriptions = array_map(function ($subscription) use ($donorAnonymousMode, $includeSensitiveData, $request) {
            $item = (new SubscriptionViewModel($subscription))->anonymousMode($donorAnonymousMode)->includeSensitiveData($includeSensitiveData)->exports();

            return $this->prepare_response_for_collection(
                $this->prepare_item_for_response($item, $request)
            );
        }, $subscriptions);

        $totalSubscriptions = empty($subscriptions) ? 0 : $totalQuery->count();
        $totalPages = (int) ceil($totalSubscriptions / $perPage);

        $response = rest_ensure_response($subscriptions);
        $response->header('X-WP-Total', $totalSubscriptions);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url(SubscriptionRoute::BASE)
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
    public function get_item($request)
    {
        $subscription = Subscription::find($request->get_param('id'));

        if (! $subscription) {
            return new WP_Error('subscription_not_found', __('Subscription not found', 'give'), ['status' => 404]);
        }

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));

        $item = (new SubscriptionViewModel($subscription))->anonymousMode($donorAnonymousMode)->includeSensitiveData($includeSensitiveData)->exports();

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Update a single donation.
     *
     * @unreleased
     */
    public function update_item($request): WP_REST_Response
    {
        $subscription = Subscription::find($request->get_param('id'));

        if (! $subscription) {
            return new WP_REST_Response(__('Subscription not found', 'give'), 404);
        }

        $nonEditableFields = [
            'id',
            'createdAt',
            'mode',
            'gatewayId',
            'gatewaySubscriptionId',
        ];

        foreach ($request->get_params() as $key => $value) {
            if (! in_array($key, $nonEditableFields, true)) {
                if (in_array($key, $subscription::propertyKeys(), true)) {
                    try {
                        $processedValue = SubscriptionFields::processValue($key, $value);
                        if ($subscription->isPropertyTypeValid($key, $processedValue)) {
                            $subscription->$key = $processedValue;
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        if ($subscription->isDirty()) {
            $subscription->save();
        }

        $item = (new SubscriptionViewModel($subscription))->exports();

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Delete a single subscription.
     *
     * @unreleased
     */
    public function delete_item($request): WP_REST_Response
    {
        $subscription = Subscription::find($request->get_param('id'));
        $force = $request->get_param('force');

        if (! $subscription) {
            return new WP_REST_Response(['message' => __('Subscription not found', 'give')], 404);
        }

        $item = (new SubscriptionViewModel($subscription))->exports();

        if ($force) {
            // Permanently delete the subscription
            $deleted = $subscription->delete();

            if (! $deleted) {
                return new WP_REST_Response(['message' => __('Failed to delete subscription', 'give')], 500);
            }
        } else {
            // Move the subscription to trash (soft delete)
            $trashed = $subscription->trash();

            if (! $trashed) {
                return new WP_REST_Response(['message' => __('Failed to trash subscription', 'give')], 500);
            }
        }

        return new WP_REST_Response(['deleted' => true, 'previous' => $item], 200);
    }

    /**
     * Delete multiple subscriptions.
     *
     * @unreleased
     */
    public function deleteItems($request): WP_REST_Response
    {
        $ids = $request->get_param('ids');
        $force = $request->get_param('force');
        $deleted = [];
        $errors = [];

        foreach ($ids as $id) {
            $subscription = Subscription::find($id);

            if (! $subscription) {
                $errors[] = ['id' => $id, 'message' => __('Subscription not found', 'give')];

                continue;
            }

            $item = (new SubscriptionViewModel($subscription))->exports();

            if ($force) {
                if ($subscription->delete()) {
                    $deleted[] = ['id' => $id, 'previous' => $item];
                } else {
                    $errors[] = ['id' => $id, 'message' => __('Failed to delete subscription', 'give')];
                }
            } else {
                $trashed = $subscription->trash();

                if ($trashed) {
                    $deleted[] = ['id' => $id, 'previous' => $item];
                } else {
                    $errors[] = ['id' => $id, 'message' => __('Failed to trash subscription', 'give')];
                }
            }
        }

        return new WP_REST_Response([
            'deleted' => $deleted,
            'errors' => $errors,
            'total_requested' => count($ids),
            'total_deleted' => count($deleted),
            'total_errors' => count($errors),
        ], 200);
    }



    /**
     * @unreleased
     */
    public function getSortColumn(string $sortColumn): string
    {
        $sortColumnsMap = [
            'id' => 'id',
            'createdAt' => 'created',
            //'updatedAt' => 'created', // subscriptions table doesn't have updated column, use created
            'status' => 'status',
            'amount' => 'recurring_amount',
            //'feeAmountRecovered' => 'recurring_fee_amount',
            'donorId' => 'customer_id',
            //'firstName' => 'customer_id', // would need join for actual firstName
            //'lastName' => 'customer_id',  // would need join for actual lastName
        ];

        return $sortColumnsMap[$sortColumn] ?? 'id';
    }

    /**
     * @unreleased
     */
    public function get_collection_params(): array
    {
        $params = parent::get_collection_params();

        $params['page']['default'] = 1;
        $params['per_page']['default'] = 30;

        // Remove default parameters not being used
        unset($params['context']);
        unset($params['search']);

        $params += give(GetSubscriptionCollectionParams::class)();

        return $params;
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $subscriptionId = $request->get_param('id') ?? $item['id'] ?? null;

        if ($subscriptionId) {
            $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $subscriptionId));
            $donor_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'donors', $item['donorId']));
            //$campaign_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'campaigns', $item['campaignId']));
            $donationForm_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'forms', $item['donationFormId']));
            $links = [
                'self' => ['href' => $self_url],
                CURIE::relationUrl('donor') => [
                    'href' => $donor_url,
                    'embeddable' => true,
                ],
                /*CURIE::relationUrl('campaign') => [
                    'href' => $campaign_url,
                    'embeddable' => true,
                ],*/
                CURIE::relationUrl('form') => [
                    'href' => $donationForm_url,
                    'embeddable' => true,
                ],
            ];
        } else {
            $links = [];
        }

        $response = new WP_REST_Response($item);
        if (! empty($links)) {
            $response->add_links($links);
        }

        return $response;
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function get_items_permissions_check($request)
    {
        return SubscriptionPermissions::validationForGetMethods($request);
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function get_item_permissions_check($request)
    {
        return SubscriptionPermissions::validationForGetMethods($request);
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function update_item_permissions_check($request)
    {
        return SubscriptionPermissions::validationForUpdateMethod($request);
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function delete_item_permissions_check($request)
    {
        return SubscriptionPermissions::validationForDeleteMethods($request);
    }

    /**
     * @unreleased
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function delete_items_permissions_check($request)
    {
        return SubscriptionPermissions::validationForDeleteMethods($request);
    }

    /**
     * @unreleased
     */
    public function get_item_schema(): array
    {
        return give(GetSubscriptionItemSchema::class)();
    }
}

<?php

namespace Give\API\REST\V3\Routes\Subscriptions;

use Exception;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\API\REST\V3\Routes\Subscriptions\Actions\GetSubscriptionCollectionParams;
use Give\API\REST\V3\Routes\Subscriptions\Actions\GetSubscriptionItemSchema;
use Give\API\REST\V3\Routes\Subscriptions\Actions\GetSubscriptionSharedParamsForGetMethods;
use Give\API\REST\V3\Routes\Subscriptions\DataTransferObjects\SubscriptionCreateData;
use Give\API\REST\V3\Routes\Subscriptions\Exceptions\SubscriptionValidationException;
use Give\API\REST\V3\Routes\Subscriptions\Fields\SubscriptionFields;
use Give\API\REST\V3\Routes\Subscriptions\Permissions\SubscriptionPermissions;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\API\REST\V3\Support\CURIE;
use Give\API\REST\V3\Support\Headers;
use Give\API\REST\V3\Support\Item;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\SubscriptionQuery;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
use Give\Subscriptions\ViewModels\SubscriptionViewModel;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * The methods using snake case like register_routes() are present in the base class,
 * and the methods using camel case like deleteItems() are available only on this class.
 *
 * @since 4.8.0
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
        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
                'args' => array_merge($this->get_collection_params(), give(GetSubscriptionSharedParamsForGetMethods::class)()),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => rest_get_endpoint_args_for_schema($this->get_item_schema(), WP_REST_Server::CREATABLE),
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
                        'description' => __('The subscription ID.', 'give'),
                        'type' => 'integer',
                        'required' => true,
                    ],
                    '_embed' => [
                        'description' => __(
                            'Whether to embed related resources in the response. It can be true when we want to embed all available resources, or a string like "givewp:donor" when we wish to embed only a specific one.',
                            'give'
                        ),
                        'type' => [
                            'string',
                            'boolean',
                        ],
                        'default' => false,
                    ],
                ], give(GetSubscriptionSharedParamsForGetMethods::class)()),
            ],
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permissions_check'],
                'args' => rest_get_endpoint_args_for_schema($this->get_item_schema(), WP_REST_Server::EDITABLE),
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
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/cancel', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'cancel_item'],
                'permission_callback' => [$this, 'cancel_item_permissions_check'],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'trash' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => __('Whether to also move the subscription to trash (trash=true) instead of just canceling it.', 'give'),
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ],
        ]);
    }

    /**
     * Get subscriptions.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
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

        if (!in_array('any', (array) $status, true)) {
            $query->whereStatus((array)$status);
        }

        if (in_array($sortColumn, ['firstName', 'lastName'], true)) {
            $query->selectDonorNames();
        }

        $totalQuery = $query->clone();
        $query->limit($perPage)->offset(($page - 1) * $perPage)->orderBy($sortColumn, $sortDirection);

        $subscriptions = $query->getAll() ?? [];

        $subscriptions = array_map(function ($subscription) use ($donorAnonymousMode, $includeSensitiveData, $request) {
            $item = (new SubscriptionViewModel($subscription))
                ->anonymousMode($donorAnonymousMode)
                ->includeSensitiveData($includeSensitiveData)
                ->exports();

            return $this->prepare_response_for_collection(
                $this->prepare_item_for_response($item, $request)
            );
        }, $subscriptions);

        $totalSubscriptions = empty($subscriptions) ? 0 : $totalQuery->count();
        $response = rest_ensure_response($subscriptions);
        $response = Headers::addPagination($response, $request, $totalSubscriptions, $perPage, $this->rest_base);

        return $response;
    }

    /**
     * Get a subscription.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function get_item($request)
    {
        $subscription = Subscription::find($request->get_param('id'));

        if (!$subscription) {
            return new WP_Error('subscription_not_found', __('Subscription not found', 'give'), ['status' => 404]);
        }

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));

        $item = (new SubscriptionViewModel($subscription))
            ->anonymousMode($donorAnonymousMode)
            ->includeSensitiveData($includeSensitiveData)
            ->exports();

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Create a subscription.
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
        try {
            $data = SubscriptionCreateData::fromRequest($request);
            $subscription = $data->createSubscription();

            $fieldsUpdate = $this->update_additional_fields_for_object($subscription, $request);

            if (is_wp_error($fieldsUpdate)) {
                return $fieldsUpdate;
            }
        } catch (SubscriptionValidationException $e) {
            return new WP_REST_Response([
                'message' => $e->getMessage(),
                'error' => $e->getErrorCode()
            ], $e->getStatusCode());
        } catch (Exception $e) {
            return new WP_REST_Response([
                'message' => sprintf(__('Failed to create subscription: %s', 'give'), $e->getMessage()),
                'error' => 'internal_server_error'
            ], 500);
        }

        $item = (new SubscriptionViewModel($subscription))
            ->includeSensitiveData(true)
            ->exports();

        $response = $this->prepare_item_for_response($item, $request);
        $response->set_status(201);

        return rest_ensure_response($response);
    }

    /**
     * Update a subscription.
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
        $subscription = Subscription::find($request->get_param('id'));

        if (!$subscription) {
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
            if (!in_array($key, $nonEditableFields, true)) {
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

        $fieldsUpdate = $this->update_additional_fields_for_object($subscription, $request);

        if (is_wp_error($fieldsUpdate)) {
            return $fieldsUpdate;
        }

        $item = (new SubscriptionViewModel($subscription))->includeSensitiveData(true)->exports();

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Delete a subscription.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function delete_item($request): WP_REST_Response
    {
        $subscription = Subscription::find($request->get_param('id'));
        $force = $request->get_param('force');

        if (!$subscription) {
            return new WP_REST_Response(['message' => __('Subscription not found', 'give')], 404);
        }

        $item = (new SubscriptionViewModel($subscription))->exports();

        if ($force) { // Permanently delete the subscription
            $deleted = $subscription->delete();

            if (!$deleted) {
                return new WP_REST_Response(['message' => __('Failed to delete subscription', 'give')], 500);
            }
        } else { // Move the subscription to trash (soft delete)
            $trashed = $subscription->trash();

            if (!$trashed) {
                return new WP_REST_Response(['message' => __('Failed to trash subscription', 'give')], 500);
            }
        }

        return new WP_REST_Response(['deleted' => true, 'previous' => $item], 200);
    }

    /**
     * Delete multiple subscriptions.
     *
     * @since 4.8.0
     *
     * @param WP_REST_Request $request Full data about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     *
     * @throws Exception
     */
    public function deleteItems($request): WP_REST_Response
    {
        $ids = $request->get_param('ids');
        $force = $request->get_param('force');
        $deleted = [];
        $errors = [];

        foreach ($ids as $id) {
            $subscription = Subscription::find($id);

            if (!$subscription) {
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
     * Cancel a subscription.
     *
     * @since 4.8.0
     */
    public function cancel_item($request)
    {
        $subscription = Subscription::find($request->get_param('id'));

        if (!$subscription) {
            return new WP_REST_Response(__('Subscription not found', 'give'), 404);
        }

        try {
            if (give()->gateways->hasPaymentGateway($subscription->gatewayId)) {
                $subscription->cancel(true);
            } else {
                $subscription->status = SubscriptionStatus::CANCELLED();
                $subscription->save();
            }

            $trash = $request->get_param('trash');

            if ($trash) {
                $subscription->trash();
            }

            $item = (new SubscriptionViewModel($subscription))->includeSensitiveData(true)->exports();
            $response = $this->prepare_item_for_response($item, $request);

            return rest_ensure_response($response);
        } catch (Exception $e) {
            return new WP_REST_Response(__('Failed to cancel subscription', 'give'), 500);
        }
    }

    /**
     * @since 4.8.0
     */
    public function getSortColumn(string $sortColumn): string
    {
        $sortColumnsMap = [
            'id' => 'id',
            'createdAt' => 'created',
            'renewsAt' => 'expiration',
            'status' => 'status',
            'amount' => 'recurring_amount',
            'feeAmountRecovered' => 'recurring_fee_amount',
            'donorId' => 'customer_id',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
        ];

        return $sortColumnsMap[$sortColumn] ?? 'id';
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

        $params += give(GetSubscriptionCollectionParams::class)();

        return $params;
    }

    /**
     * @since 4.10.0 added embeddable links for campaign and form
     * @since 4.8.0
     *
     * @param mixed           $item    WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function prepare_item_for_response($item, $request)
    {
        try {
            $subscriptionId = $request->get_param('id') ?? $item['id'] ?? null;

            if ($subscriptionId && $subscription = Subscription::find($subscriptionId)) {
                $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $subscription->id));

                $links = [
                    'self' => ['href' => $self_url]
                ];

                if (!empty($item['donorId'])) {
                    $donor_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'donors', $item['donorId']));
                    $donor_url = add_query_arg([
                        'mode' => $request->get_param('mode'),
                    ], $donor_url);

                    $links[CURIE::relationUrl('donor')] = [
                        'href' => $donor_url,
                        'embeddable' => true,
                    ];
                }

                if (!empty($item['donationFormId'])) {
                    $form_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'forms', $item['donationFormId']));
                    $form_url = add_query_arg([
                        'mode' => $subscription->mode->getValue(),
                    ], $form_url);

                    $links[CURIE::relationUrl('form')] = [
                        'href' => $form_url,
                        'embeddable' => true,
                    ];
                }

                if (!empty($item['campaignId'])) {
                    $campaign_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'campaigns', $item['campaignId']));
                    $campaign_url = add_query_arg([
                        'mode' => $subscription->mode->getValue(),
                    ], $campaign_url);

                    $links[CURIE::relationUrl('campaign')] = [
                        'href' => $campaign_url,
                        'embeddable' => true,
                    ];
                }

                $donations_url = rest_url(sprintf('%s/%s', $this->namespace, 'donations'));
                $donations_url = add_query_arg([
                    'mode' => $subscription->mode->getValue(),
                    'subscriptionId' => $subscription->id,
                ], $donations_url);

                $links[CURIE::relationUrl('donations')] = [
                    'href' => $donations_url,
                    'embeddable' => true,
                ];
            } else {
                $links = [];
            }

            $response = new WP_REST_Response(Item::formatDatesForResponse($item, ['createdAt', 'renewsAt']));
            if (!empty($links)) {
                $response->add_links($links);
            }

            $response->data = $this->add_additional_fields_to_object($response->data, $request);

            return $response;
        } catch (Exception $e) {
            return new WP_Error(
                'prepare_item_for_response_error',
                sprintf(
                    __('Error while preparing subscription for response: %s', 'give'),
                    $e->getMessage()
                ),
                ['status' => 400]
            );
        }
    }

    /**
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
     *
     * @param WP_REST_Request $request
     *
     * @return true|WP_Error
     */
    public function create_item_permissions_check($request)
    {
        return SubscriptionPermissions::validationForUpdateMethod($request);
    }

    /**
     * @since 4.8.0
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
     * @since 4.8.0
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
     * @since 4.8.0
     */
    public function cancel_item_permissions_check($request)
    {
        return SubscriptionPermissions::validationForDeleteMethods($request);
    }

    /**
     * @since 4.8.0
     */
    public function get_item_schema(): array
    {
        $schema = give(GetSubscriptionItemSchema::class)();
        return $this->add_additional_fields_schema($schema);
    }
}

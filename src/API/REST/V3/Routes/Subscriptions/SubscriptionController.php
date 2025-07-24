<?php

namespace Give\API\REST\V3\Routes\Subscriptions;

use DateTime;
use Give\API\REST\V3\Routes\CURIE;
use Give\API\REST\V3\Routes\Donors\ValueObjects\DonorAnonymousMode;
use Give\API\REST\V3\Routes\Subscriptions\ValueObjects\SubscriptionRoute;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use Give\Framework\Support\ValueObjects\Money;
use Give\Subscriptions\Models\Subscription;
use Give\Subscriptions\ValueObjects\SubscriptionPeriod;
use Give\Subscriptions\ValueObjects\SubscriptionStatus;
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
                'args' => array_merge($this->get_collection_params(), $this->getSharedParams()),
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
                ], $this->getSharedParams()),
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

        $query = Subscription::query();

        // TODO: Add campaignId filter
        // if ($campaignId = $request->get_param('campaignId')) {
        //     // Filter by CampaignId
        //     $query->where('give_donationmeta_attach_meta_campaignId.meta_value', $campaignId);
        // }

        if ($donorId = $request->get_param('donorId')) {
            $query->where('customer_id', $donorId);
        }

        // Include only current payment "mode"
        $query->where('payment_mode', $mode);

        // Filter by status if not 'any'
        if (! in_array('any', (array) $status, true)) {
            $query->whereIn('status', (array) $status);
        }

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy($sortColumn, $sortDirection);

        $sql = $query->getSQL();
            
        $subscriptions = $query->getAll() ?? [];

        $subscriptions = array_map(function ($subscription) use ($request) {
            $item = (new SubscriptionViewModel($subscription))->exports();

            return $this->prepare_response_for_collection(
                $this->prepare_item_for_response($item, $request)
            );
        }, $subscriptions);

        $totalSubscriptions = empty($subscriptions) ? 0 : Subscription::query()->count();
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

        $item = (new SubscriptionViewModel($subscription))->exports();

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
                        $processedValue = $this->processFieldValue($key, $value);
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
     * Process field values for special data types before setting them on the subscription model.
     *
     * @unreleased
     */
    private function processFieldValue(string $key, $value)
    {
        switch ($key) {
            case 'amount':
            case 'feeAmountRecovered':
                if (is_array($value)) {
                    // Handle Money object array format: ['amount' => 100.00, 'currency' => 'USD']
                    if (isset($value['amount']) && isset($value['currency'])) {
                        return Money::fromDecimal($value['amount'], $value['currency']);
                    }
                }

                return $value;

            case 'status':
                if (is_string($value)) {
                    return new SubscriptionStatus($value);
                }

                return $value;

            case 'period':
                if (is_string($value)) {
                    return new SubscriptionPeriod($value);
                }

                return $value;

            case 'createdAt':
            case 'renewsAt':
                try {
                    if (is_string($value)) {
                        return new DateTime($value, wp_timezone());
                    } elseif (is_array($value)) {
                        return new DateTime($value['date'], new \DateTimeZone($value['timezone']));
                    }
                } catch (\Exception $e) {
                    throw new InvalidArgumentException("Invalid date format for {$key}: {$value}.");
                }

                return $value;

            default:
                return $value;
        }
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

        $params += [
            'sort' => [
                'type' => 'string',
                'default' => 'id',
                'enum' => [
                    'id',
                    'createdAt',
                    //'updatedAt',
                    'status',
                    'amount',
                    //'feeAmountRecovered',
                    'donorId',
                    //'firstName',
                    //'lastName',
                ],
            ],
            'direction' => [
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
            ],
            // Note: 'mode' parameter exists for API consistency but isn't used in queries
            // since subscriptions table doesn't have a mode column
            'mode' => [
                'type' => 'string',
                'default' => 'live',
                'enum' => ['live', 'test'],
            ],
            'status' => [
                'type' => 'array',
                'items' => [
                    'type' => 'string',
                    'enum' => [
                        'any',
                        'pending',
                        'active',
                        'expired',
                        'completed',
                        'failing',
                        'cancelled',
                        'suspended',
                        'paused',
                        'refunded',
                        'abandoned',
                    ],
                ],
                'default' => ['any'],
            ],
            'campaignId' => [
                'type' => 'integer',
                'default' => 0,
            ],
            'donorId' => [
                'type' => 'integer',
                'default' => 0,
            ],
        ];

        return $params;
    }
    
    /**
     * @unreleased
     */
    public function getSharedParams(): array
    {
        return [
            'includeSensitiveData' => [
                'description' => __('Include or not include data that can be used to contact or locate the donors, such as phone number, email, billing address, etc. (require proper permissions)', 'give'),
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
        ];
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
    public function permissionsCheckForGetMethods(WP_REST_Request $request)
    {        
        $isAdmin = $this->canEditSubscriptions();        

        $includeSensitiveData = $request->get_param('includeSensitiveData');
        if ( ! $isAdmin && $includeSensitiveData) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include sensitive data.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        if ($request->get_param('anonymousDonors') !== null) {
            $donorAnonymousMode = new DonorAnonymousMode($request->get_param('anonymousDonors'));
            if ( ! $isAdmin && $donorAnonymousMode->isIncluded()) {
                return new WP_Error(
                    'rest_forbidden',
                    esc_html__('You do not have permission to include anonymous donors.', 'give'),
                    ['status' => $this->authorizationStatusCode()]
                );
            }
        }        

        return true;
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
        return $this->permissionsCheckForGetMethods($request);
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
        return $this->permissionsCheckForGetMethods($request);
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
        if (! $this->canEditSubscriptions()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to update subscriptions.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );   
        }        

        return true;
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
        if (! $this->canDeleteSubscriptions()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to delete subscriptions.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
            
        }

        return true;
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
        if ($this->canDeleteSubscriptions()) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to delete subscriptions.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        return true;
    }

    /**
     * Check if current user can edit subscriptions.
     *
     * @unreleased
     */
    private function canEditSubscriptions(): bool
    {
        return current_user_can('manage_options')
               || (
                   current_user_can('edit_give_payments')
                   && current_user_can('view_give_payments')
               );
    }

    /**
     * Check if current user can delete subscriptions.
     *
     * @unreleased
     */
    private function canDeleteSubscriptions(): bool
    {
        return current_user_can('manage_options') || current_user_can('delete_give_payments');
    }

    /**
     * @unreleased
     */
    public function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * @unreleased
     */
    public function get_item_schema(): array
    {
        return [
            'title' => 'subscription',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Subscription ID', 'give'),
                ],
                'donorId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donor ID', 'give'),
                ],
                'donationFormId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donation form ID', 'give'),
                ],
                'amount' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'amount' => [
                            'type' => 'number',
                        ],
                        'amountInMinorUnits' => [
                            'type' => 'integer',
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                        ],
                    ],
                    'description' => esc_html__('Subscription amount', 'give'),
                ],
                'feeAmountRecovered' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'amount' => [
                            'type' => 'number',
                        ],
                        'amountInMinorUnits' => [
                            'type' => 'integer',
                        ],
                        'currency' => [
                            'type' => 'string',
                            'format' => 'text-field',
                        ],
                    ],
                    'description' => esc_html__('Fee amount recovered', 'give'),
                ],
                'status' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription status', 'give'),
                    'enum' => array_values(SubscriptionStatus::toArray()),
                ],
                'period' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription billing period', 'give'),
                    'enum' => ['day', 'week', 'month', 'quarter', 'year'],
                ],
                'frequency' => [
                    'type' => 'integer',
                    'description' => esc_html__('Billing frequency', 'give'),
                ],
                'installments' => [
                    'type' => 'integer',
                    'description' => esc_html__('Number of installments (0 for unlimited)', 'give'),
                ],
                'transactionId' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Transaction ID', 'give'),
                    'format' => 'text-field',
                ],
                'gatewayId' => [
                    'type' => 'string',
                    'description' => esc_html__('Payment gateway ID', 'give'),
                    'format' => 'text-field',
                ],
                'gatewaySubscriptionId' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Gateway subscription ID', 'give'),
                    'format' => 'text-field',
                ],
                'mode' => [
                    'type' => 'string',
                    'description' => esc_html__('Subscription mode (live or test)', 'give'),
                    'default' => 'live',
                    'enum' => ['live', 'test'],
                ],
                'createdAt' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'date' => [
                            'type' => 'string',
                            'description' => esc_html__('Date', 'give'),
                            'format' => 'date-time',
                        ],
                        'timezone' => [
                            'type' => 'string',
                            'description' => esc_html__('Timezone of the date', 'give'),
                            'format' => 'text-field',
                        ],
                        'timezone_type' => [
                            'type' => 'integer',
                            'description' => esc_html__('Timezone type', 'give'),
                        ],
                    ],
                    'description' => esc_html__('Subscription creation date', 'give'),
                    'format' => 'date-time',
                ],
                'renewsAt' => [
                    'type' => ['object', 'null'],
                    'properties' => [
                        'date' => [
                            'type' => 'string',
                            'description' => esc_html__('Date', 'give'),
                            'format' => 'date-time',
                        ],
                        'timezone' => [
                            'type' => 'string',
                            'description' => esc_html__('Timezone of the date', 'give'),
                            'format' => 'text-field',
                        ],
                        'timezone_type' => [
                            'type' => 'integer',
                            'description' => esc_html__('Timezone type', 'give'),
                        ],
                    ],
                    'description' => esc_html__('Next renewal date', 'give'),
                    'format' => 'date-time',
                ],
            ],
            'required' => [
                'id',
                'donorId',
                'donationFormId',
                'amount',
                'status',
                'period',
                'frequency',
                'gatewayId',
                'mode',
                'createdAt',
            ],
        ];
    }
}

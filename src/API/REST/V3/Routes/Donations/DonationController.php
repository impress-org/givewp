<?php

namespace Give\API\REST\V3\Routes\Donations;

use Exception;
use Give\API\REST\V3\Routes\Donations\DataTransferObjects\DonationCreateData;
use Give\API\REST\V3\Routes\Donations\Exceptions\DonationValidationException;
use Give\API\REST\V3\Routes\Donations\Fields\DonationFields;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationAnonymousMode;
use Give\API\REST\V3\Routes\Donations\ValueObjects\DonationRoute;
use Give\API\REST\V3\Support\CURIE;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\Donations\ViewModels\DonationViewModel;
use Give\Framework\PaymentGateways\CommandHandlers\PaymentRefundedHandler;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Contracts\PaymentGatewayRefundable;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @since 4.6.0
 */
class DonationController extends WP_REST_Controller
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
     * @since 4.6.0
     */
    public function __construct()
    {
        $this->namespace = DonationRoute::NAMESPACE;
        $this->rest_base = DonationRoute::BASE;
    }

    /**
     * @since 4.9.0 Move schema key to the route level instead of defining it for each endpoint (which is incorrect)
     * @since 4.6.0
     */
    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_item'],
                'permission_callback' => [$this, 'permissionsCheck'],
                'args' => [
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
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'includeSensitiveData' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'anonymousDonations' => [
                        'type' => 'string',
                        'default' => 'exclude',
                        'enum' => [
                            'exclude',
                            'include',
                            'redact',
                        ],
                    ],
                ],
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
                        'type' => 'integer',
                        'required' => true,
                    ],
                    'force' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Whether to permanently delete (force=true) or move to trash (force=false, default).',
                    ],
                ],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base, [
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => [$this, 'get_items'],
                'permission_callback' => [$this, 'permissionsCheck'],
                'args' => $this->get_collection_params(),
            ],
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
                'args' => rest_get_endpoint_args_for_schema($this->get_item_schema(), WP_REST_Server::CREATABLE),
            ],
            [
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => [$this, 'delete_items'],
                'permission_callback' => [$this, 'delete_items_permissions_check'],
                'args' => [
                    'ids' => [
                        'description' => __('Array of donation IDs to delete', 'give'),
                        'type' => 'array',
                        'items' => [
                            'type' => 'integer',
                        ],
                        'required' => true,
                    ],
                    'force' => [
                        'type' => 'boolean',
                        'default' => false,
                        'description' => 'Whether to permanently delete (force=true) or move to trash (force=false, default).',
                    ],
                ],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);

        register_rest_route($this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/refund', [
            [
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => [$this, 'refund_item'],
                'permission_callback' => [$this, 'refund_item_permissions_check'],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                    ],
                ],
            ],
            'schema' => [$this, 'get_public_item_schema'],
        ]);
    }

    /**
     * @since 4.6.0
     */
    public function get_items($request)
    {
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donationAnonymousMode = new DonationAnonymousMode($request->get_param('anonymousDonations'));
        $page = $request->get_param('page');
        $perPage = $request->get_param('per_page');
        $sortColumn = $this->getSortColumn($request->get_param('sort'));
        $sortDirection = $request->get_param('direction');
        $mode = $request->get_param('mode');
        $status = $request->get_param('status');

        $query = Donation::query();

        if ($campaignId = $request->get_param('campaignId')) {
            // Filter by CampaignId
            $query->where('give_donationmeta_attach_meta_campaignId.meta_value', $campaignId);
        }

        if ($donorId = $request->get_param('donorId')) {
            $query->where('give_donationmeta_attach_meta_donorId.meta_value', $donorId);
        }

        if ($subscriptionId = $request->get_param('subscriptionId')) {
            $query->where('give_donationmeta_attach_meta_subscriptionId.meta_value', $subscriptionId);
        }

        if ($donationAnonymousMode->isExcluded()) {
            // Exclude anonymous donations from results
            $query->where('give_donationmeta_attach_meta_anonymous.meta_value', 0);
        }

        // Include only current payment "mode"
        $query->where('give_donationmeta_attach_meta_mode.meta_value', $mode);

        // Filter by status if not 'any'
        if (!in_array('any', (array)$status, true)) {
            $query->whereIn('post_status', (array)$status);
        }

        $query
            ->limit($perPage)
            ->offset(($page - 1) * $perPage)
            ->orderBy($sortColumn, $sortDirection);

        $donations = $query->getAll() ?? [];
        $donations = array_map(function ($donation) use ($includeSensitiveData, $donationAnonymousMode, $request) {
            $item = (new DonationViewModel($donation))
                ->anonymousMode($donationAnonymousMode)
                ->includeSensitiveData($includeSensitiveData)
                ->exports();

            return $this->prepare_response_for_collection(
                $this->prepare_item_for_response($item, $request)
            );
        }, $donations);

        $totalDonations = empty($donations) ? 0 : Donation::query()->count();
        $totalPages = (int)ceil($totalDonations / $perPage);

        $response = rest_ensure_response($donations);
        $response->header('X-WP-Total', $totalDonations);
        $response->header('X-WP-TotalPages', $totalPages);

        $base = add_query_arg(
            map_deep($request->get_query_params(), function ($value) {
                if (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                }

                return urlencode($value);
            }),
            rest_url(DonationRoute::BASE)
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
     * @since 4.6.0
     */
    public function get_item($request)
    {
        $donation = Donation::find($request->get_param('id'));
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $donationAnonymousMode = new DonationAnonymousMode($request->get_param('anonymousDonations'));

        if (!$donation || ($donation->anonymous && $donationAnonymousMode->isExcluded())) {
            return new WP_Error('donation_not_found', __('Donation not found', 'give'), ['status' => 404]);
        }

        $item = (new DonationViewModel($donation))
            ->anonymousMode($donationAnonymousMode)
            ->includeSensitiveData($includeSensitiveData)
            ->exports();

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Create a single donation.
     *
     * @since 4.8.0
     */
    public function create_item($request): WP_REST_Response
    {
        try {
            $data = DonationCreateData::fromRequest($request);
            $donation = $data->isRenewal() ? $data->createRenewal() : $data->createDonation();

            $item = (new DonationViewModel($donation))
                ->includeSensitiveData(true)
                ->anonymousMode(new DonationAnonymousMode('include'))
                ->exports();

            $response = $this->prepare_item_for_response($item, $request);
            $response->set_status(201);

            return rest_ensure_response($response);
        } catch (DonationValidationException $e) {
            return new WP_REST_Response([
                'message' => $e->getMessage(),
                'error' => $e->getErrorCode()
            ], $e->getStatusCode());
        } catch (\Exception $e) {
            return new WP_REST_Response([
                'message' => __('Failed to create donation', 'give'),
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Update a single donation.
     *
     * @since 4.7.0 Add support for updating custom fields
     * @since 4.6.0
     *
     * @return WP_REST_Response|WP_Error
     */
    public function update_item($request)
    {
        $donation = Donation::find($request->get_param('id'));

        if (!$donation) {
            return new WP_REST_Response(__('Donation not found', 'give'), 404);
        }

        $nonEditableFields = [
            'id',
            'updatedAt',
            'purchaseKey',
            'donorIp',
            'type',
            'mode',
            'gatewayTransactionId',
        ];

        foreach ($request->get_params() as $key => $value) {
            if (!in_array($key, $nonEditableFields, true)) {
                if (in_array($key, $donation::propertyKeys(), true)) {
                    try {
                        $processedValue = DonationFields::processValue($key, $value);
                        if ($donation->isPropertyTypeValid($key, $processedValue)) {
                            $donation->$key = $processedValue;
                        }
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        if ($donation->isDirty()) {
            $donation->save();
        }

        $item = (new DonationViewModel($donation))
            ->includeSensitiveData(true)
            ->anonymousMode(new DonationAnonymousMode('include'))
            ->exports();

        $fieldsUpdate = $this->update_additional_fields_for_object($item, $request);
        if (is_wp_error($fieldsUpdate)) {
            return $fieldsUpdate;
        }

        $response = $this->prepare_item_for_response($item, $request);

        return rest_ensure_response($response);
    }

    /**
     * Refund a single donation.
     *
     * @since 4.6.0
     */
    public function refund_item($request)
    {
        $donation = Donation::find($request->get_param('id'));

        if (!$donation) {
            return new WP_REST_Response(__('Donation not found', 'give'), 404);
        }

        $gateway = $donation->gateway();

        if (!$gateway->supportsRefund()) {
            return new WP_REST_Response(__('Refunds are not supported for this gateway', 'give'), 400);
        }

        try {
            /** @var PaymentGatewayRefundable $gateway */
            $command = $gateway->refundDonation($donation);

            if ($command instanceof PaymentRefunded) {
                $handler = new PaymentRefundedHandler($command);
                $handler->handle($donation);
            }

            $response = $this->prepare_item_for_response($donation->toArray(), $request);

            return rest_ensure_response($response);
        } catch (\Exception $exception) {
            return new WP_REST_Response(__('Failed to refund donation', 'give'), 500);
        }
    }

    /**
     * Delete a single donation.
     *
     * @since 4.6.0
     */
    public function delete_item($request): WP_REST_Response
    {
        $donation = Donation::find($request->get_param('id'));
        $force = $request->get_param('force');

        if (!$donation) {
            return new WP_REST_Response(['message' => __('Donation not found', 'give')], 404);
        }

        $item = (new DonationViewModel($donation))
            ->includeSensitiveData(true)
            ->anonymousMode(new DonationAnonymousMode('include'))
            ->exports();

        if ($force) {
            // Permanently delete the donation
            $deleted = $donation->delete();

            if (!$deleted) {
                return new WP_REST_Response(['message' => __('Failed to delete donation', 'give')], 500);
            }
        } else {
            // Move the donation to trash (soft delete)
            $trashed = $donation->trash();

            if (!$trashed) {
                return new WP_REST_Response(['message' => __('Failed to trash donation', 'give')], 500);
            }
        }

        return new WP_REST_Response(['deleted' => true, 'previous' => $item], 200);
    }

    /**
     * Delete multiple donations.
     *
     * @since 4.6.0
     */
    public function delete_items($request): WP_REST_Response
    {
        $ids = $request->get_param('ids');
        $force = $request->get_param('force');
        $deleted = [];
        $errors = [];

        foreach ($ids as $id) {
            $donation = Donation::find($id);

            if (!$donation) {
                $errors[] = ['id' => $id, 'message' => __('Donation not found', 'give')];
                continue;
            }

            $item = (new DonationViewModel($donation))
                ->includeSensitiveData(true)
                ->anonymousMode(new DonationAnonymousMode('include'))
                ->exports();

            if ($force) {
                if ($donation->delete()) {
                    $deleted[] = ['id' => $id, 'previous' => $item];
                } else {
                    $errors[] = ['id' => $id, 'message' => __('Failed to delete donation', 'give')];
                }
            } else {
                $trashed = $donation->trash();

                if ($trashed) {
                    $deleted[] = ['id' => $id, 'previous' => $item];
                } else {
                    $errors[] = ['id' => $id, 'message' => __('Failed to trash donation', 'give')];
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
     * @since 4.6.0
     */
    public function getSortColumn(string $sortColumn): string
    {
        $sortColumnsMap = [
            'id' => 'ID',
            'createdAt' => 'post_date',
            'updatedAt' => 'post_modified',
            'status' => 'post_status',
            'amount' => 'give_donationmeta_attach_meta_amount.meta_value',
            'feeAmountRecovered' => 'give_donationmeta_attach_meta_feeAmountRecovered.meta_value',
            'donorId' => 'give_donationmeta_attach_meta_donorId.meta_value',
            'firstName' => 'give_donationmeta_attach_meta_firstName.meta_value',
            'lastName' => 'give_donationmeta_attach_meta_lastName.meta_value',
        ];

        return $sortColumnsMap[$sortColumn];
    }

    /**
     * @since 4.6.0
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
                    'updatedAt',
                    'status',
                    'amount',
                    'feeAmountRecovered',
                    'donorId',
                    'firstName',
                    'lastName',
                ],
            ],
            'direction' => [
                'type' => 'string',
                'default' => 'DESC',
                'enum' => ['ASC', 'DESC'],
            ],
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
                        'publish',
                        'give_subscription',
                        'pending',
                        'processing',
                        'refunded',
                        'revoked',
                        'failed',
                        'cancelled',
                        'abandoned',
                        'preapproval',
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
            'subscriptionId' => [
                'type' => 'integer',
                'default' => 0,
            ],
            'includeSensitiveData' => [
                'type' => 'boolean',
                'default' => false,
            ],
            'anonymousDonations' => [
                'type' => 'string',
                'default' => 'exclude',
                'enum' => [
                    'exclude',
                    'include',
                    'redact',
                ],
            ],
            'force' => [
                'type' => 'boolean',
                'default' => false,
                'description' => 'Whether to permanently delete (force=true) or move to trash (force=false, default).',
            ],
        ];

        return $params;
    }

    /**
     * @since 4.7.0 Add support for adding custom fields to the response
     * @since 4.6.0
     * @throws Exception
     */
    public function prepare_item_for_response($item, $request): WP_REST_Response
    {
        $donationId = $request->get_param('id') ?? $item['id'] ?? null;

        if ($donationId) {
            $self_url = rest_url(sprintf('%s/%s/%d', $this->namespace, $this->rest_base, $donationId));
            $donor_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'donors', $item['donorId']));
            $campaign_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'campaigns', $item['campaignId']));
            $links = [
                'self' => ['href' => $self_url],
                CURIE::relationUrl('donor') => [
                    'href' => $donor_url,
                    'embeddable' => true,
                ],
                CURIE::relationUrl('campaign') => [
                    'href' => $campaign_url,
                    'embeddable' => true,
                ],
            ];

            // Add subscription link when subscriptionId is greater than 0
            if (isset($item['subscriptionId']) && $item['subscriptionId'] > 0) {
                $subscription_url = rest_url(sprintf('%s/%s/%d', $this->namespace, 'subscriptions', $item['subscriptionId']));
                $links[CURIE::relationUrl('subscription')] = [
                    'href' => $subscription_url,
                    'embeddable' => true,
                ];
            }
        } else {
            $links = [];
        }

        $response = new WP_REST_Response($item);
        if (!empty($links)) {
            $response->add_links($links);
        }

        $response->data = $this->add_additional_fields_to_object($response->data, $request);

        return $response;
    }

    /**
     * @since 4.6.0
     */
    public function permissionsCheck(WP_REST_Request $request)
    {
        $includeSensitiveData = $request->get_param('includeSensitiveData');
        $includeAnonymousDonations = $request->get_param('anonymousDonations');
        $canEditDonations = $this->canEditDonations();

        if ($includeSensitiveData && !$canEditDonations) {
            return new WP_Error(
                'rest_forbidden',
                esc_html__('You do not have permission to include sensitive data.', 'give'),
                ['status' => $this->authorizationStatusCode()]
            );
        }

        if ($includeAnonymousDonations !== null) {
            $anonymousMode = new DonationAnonymousMode($includeAnonymousDonations);

            if ($anonymousMode->isIncluded() && !$canEditDonations) {
                return new WP_Error(
                    'rest_forbidden',
                    esc_html__('You do not have permission to include anonymous donations.', 'give'),
                    ['status' => $this->authorizationStatusCode()]
                );
            }
        }

        return true;
    }

    /**
     * @since 4.6.0
     */
    public function update_item_permissions_check($request)
    {
        if ($this->canEditDonations()) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__('You do not have permission to update donations.', 'give'),
            ['status' => $this->authorizationStatusCode()]
        );
    }

    /**
     * @since 4.6.0
     */
    public function create_item_permissions_check($request)
    {
        if ($this->canEditDonations()) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__('You do not have permission to create donations.', 'give'),
            ['status' => $this->authorizationStatusCode()]
        );
    }

    /**
     * @since 4.8.0
     */
    public function delete_item_permissions_check($request)
    {
        if ($this->canDeleteDonations()) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__('You do not have permission to delete donations.', 'give'),
            ['status' => $this->authorizationStatusCode()]
        );
    }

    /**
     * @since 4.6.0
     */
    public function delete_items_permissions_check($request)
    {
        if ($this->canDeleteDonations()) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__('You do not have permission to delete donations.', 'give'),
            ['status' => $this->authorizationStatusCode()]
        );
    }

    /**
     * @since 4.6.0
     */
    public function refund_item_permissions_check($request)
    {
        if ($this->canRefundDonations()) {
            return true;
        }

        return new WP_Error(
            'rest_forbidden',
            esc_html__('You do not have permission to refund donations.', 'give'),
            ['status' => $this->authorizationStatusCode()]
        );
    }

    /**
     * Check if current user can edit donations.
     *
     * @since 4.6.0
     */
    private function canEditDonations(): bool
    {
        return current_user_can('manage_options')
            || (
                current_user_can('edit_give_payments')
                && current_user_can('view_give_payments')
            );
    }

    /**
     * Check if current user can delete donations.
     *
     * @since 4.6.0
     */
    private function canDeleteDonations(): bool
    {
        return current_user_can('manage_options') || current_user_can('delete_give_payments');
    }

    /**
     * Check if current user can refund donations.
     *
     * @since 4.6.0
     */
    private function canRefundDonations(): bool
    {
        return current_user_can('manage_options') || current_user_can('edit_give_payments');
    }

    /**
     * @since 4.6.0
     */
    public function authorizationStatusCode(): int
    {
        return is_user_logged_in() ? 403 : 401;
    }

    /**
     * @since 4.8.0 Change default status to complete
     * @since 4.7.0 Change title to givewp/donation and add custom fields schema
     * @since 4.6.1 Change type of billing address properties to accept null values
     * @since 4.6.0
     */
    public function get_item_schema(): array
    {
        $schema = [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/donation',
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donation ID', 'give'),
                    'readonly' => true,
                ],

                'donorId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Donor ID', 'give'),
                ],
                'firstName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor first name', 'give'),
                    'format' => 'text-field',
                ],
                'lastName' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor last name', 'give'),
                    'format' => 'text-field',
                ],
                'honorific' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor honorific/prefix', 'give'),
                    'enum' => $this->get_honorific_prefixes(),
                ],
                'email' => [
                    'type' => 'string',
                    'description' => esc_html__('Donor email', 'give'),
                    'format' => 'email',
                ],
                'phone' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor phone', 'give'),
                    'format' => 'text-field',
                ],
                'company' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor company', 'give'),
                    'format' => 'text-field',
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
                    'description' => esc_html__('Donation amount', 'give'),
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
                'eventTicketsAmount' => [
                    'type' => ['object', 'null'],
                    'readonly' => true,
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
                    'description' => esc_html__('Event tickets amount', 'give'),
                ],
                'status' => [
                    'type' => 'string',
                    'description' => esc_html__('Donation status', 'give'),
                    'enum' => array_values(DonationStatus::toArray()),
                    'default' => DonationStatus::COMPLETE,
                ],
                'type' => [
                    'type' => 'string',
                    'description' => esc_html__('Donation type', 'give'),
                    'enum' => array_values(DonationType::toArray()),
                    'default' => DonationType::SINGLE,
                ],
                'gatewayId' => [
                    'type' => 'string',
                    'description' => esc_html__('Payment gateway ID', 'give'),
                    'format' => 'text-field',
                ],
                'mode' => [
                    'type' => 'string',
                    'description' => esc_html__('Donation mode (live or test)', 'give'),
                    'enum' => ['live', 'test'],
                ],
                'anonymous' => [
                    'type' => 'boolean',
                    'description' => esc_html__('Whether the donation is anonymous', 'give'),
                    'default' => false,
                ],
                'campaignId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Campaign ID', 'give'),
                ],
                'formId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Form ID', 'give'),
                ],
                'formTitle' => [
                    'type' => 'string',
                    'description' => esc_html__('Form title', 'give'),
                    'format' => 'text-field',
                ],
                'subscriptionId' => [
                    'type' => 'integer',
                    'description' => esc_html__('Subscription ID', 'give'),
                    'default' => 0,
                ],
                'levelId' => [
                    'type' => 'string',
                    'description' => esc_html__('Level ID', 'give'),
                    'format' => 'text-field',
                ],
                'gatewayTransactionId' => [
                    'type' => 'string',
                    'description' => esc_html__('Gateway transaction ID', 'give'),
                    'format' => 'text-field',
                ],
                'exchangeRate' => [
                    'type' => 'string',
                    'description' => esc_html__('Exchange rate', 'give'),
                    'format' => 'text-field',
                ],
                'comment' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donation comment', 'give'),
                    'format' => 'text-field',
                ],
                'billingAddress' => [
                    'type' => ['object', 'null'],
                    'description' => esc_html__('Billing address', 'give'),
                    'properties' => [
                        'address1' => ['type' => ['string', 'null'], 'format' => 'text-field'],
                        'address2' => ['type' => ['string', 'null'], 'format' => 'text-field'],
                        'city' => ['type' => ['string', 'null'], 'format' => 'text-field'],
                        'state' => ['type' => ['string', 'null'], 'format' => 'text-field'],
                        'country' => ['type' => ['string', 'null'], 'format' => 'text-field'],
                        'zip' => ['type' => ['string', 'null'], 'format' => 'text-field'],
                    ],
                ],
                'donorIp' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Donor IP address (sensitive data)', 'give'),
                    'format' => 'text-field',
                ],
                'purchaseKey' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Purchase key (sensitive data)', 'give'),
                    'format' => 'text-field',
                ],
                'createdAt' => [
                    'oneOf' => [
                        [
                            'type' => 'string',
                            'description' => esc_html__('Donation creation date as ISO string', 'give'),
                            'format' => 'date-time',
                        ],
                        [
                            'type' => 'object',
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
                            'description' => esc_html__('Donation creation date', 'give'),
                        ],
                        [
                            'type' => 'null',
                        ],
                    ],
                ],
                'updatedAt' => [
                    'oneOf' => [
                        [
                            'type' => 'string',
                            'description' => esc_html__('Donation last update date as ISO string', 'give'),
                            'format' => 'date-time',
                        ],
                        [
                            'type' => 'object',
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
                            'description' => esc_html__('Donation last update date', 'give'),
                        ],
                        [
                            'type' => 'null',
                        ],
                    ],
                ],
                'updateRenewalDate' => [
                    'type' => 'boolean',
                    'description' => esc_html__('Whether to update the subscription renewal date with the createdAt date when creating subscription or renewal donations', 'give'),
                    'default' => false,
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
            ],
        ];

        return $this->add_additional_fields_schema($schema);
    }

    /**
     * Gets all available honorific prefixes.
     *
     * Fetches the user-configured honorific prefixes from settings and merges them
     * with a hardcoded 'anonymous' prefix. The 'anonymous' prefix is required
     * when requests with anonymousDonations=redact are present.
     *
     * @return array<string, string> An associative array of honorific prefixes.
     */
    private function get_honorific_prefixes(): array {
        $prefixes = (array) give_get_option( 'title_prefixes', array_values( give_get_default_title_prefixes() ) );

        return array_merge( $prefixes, [
            'anonymous' => __( 'anonymous', 'give' ),
        ] );
    }
}

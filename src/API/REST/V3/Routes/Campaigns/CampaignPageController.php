<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\API\REST\V3\Support\Item;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\Models\CampaignPage;
use Give\Campaigns\ValueObjects\CampaignPageStatus;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CampaignPageController extends WP_REST_Controller
{
    /**
     * @var string
     */
    protected $namespace;

    public function __construct()
    {
        $this->namespace = CampaignRoute::NAMESPACE;
    }

    /**
     * @unreleased
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . CampaignRoute::CAMPAIGN . '/page',
            [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'create_item'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                    'args' => [
                        'id' => [
                            'type' => 'integer',
                            'required' => true,
                        ],
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @return WP_REST_Response|WP_Error
     */
    public function create_item($request)
    {
        try {
            $campaignId = (int) $request->get_param('id');

            $campaign = Campaign::find($campaignId);

            if (!$campaign) {
                return new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);
            }

            // Create a new campaign page in draft status and associate to campaign
            $page = $campaign->createPage([
                'status' => CampaignPageStatus::DRAFT(),
            ]);

            $item = $page->toArray();

            $response = $this->prepare_item_for_response($item, $request);
            $response->set_status(201);

            return rest_ensure_response($response);
        } catch (Exception $exception) {
            return new WP_Error('create_campaign_page_error', __('Error while creating campaign page', 'give'), ['status' => 400]);
        }
    }

    /**
     * @unreleased
     */
    public function prepare_item_for_response($item, $request)
    {
        try {
            $campaignId = $item['campaignId'] ?? $request->get_param('id') ?? null;

            if ($campaignId) {
                $self_url = rest_url(sprintf('%s/campaigns/%d/page', $this->namespace, $campaignId));

                $links = [
                    'self' => ['href' => $self_url],
                ];
            } else {
                $links = [];
            }

            $itemWithDatesFormatted = Item::formatDatesForResponse($item, ['createdAt', 'updatedAt']);

            $response = new WP_REST_Response($itemWithDatesFormatted);

            if (!empty($links)) {
                $response->add_links($links);
            }

            $response->data = $this->add_additional_fields_to_object($response->data, $request);

            return $response;
        } catch (Exception $e) {
            return new WP_Error(
                'prepare_item_for_response_error',
                sprintf(
                    __('Error while preparing campaign page for response: %s', 'give'),
                    $e->getMessage()
                ),
                ['status' => 400]
            );
        }
    }

    /**
     * @unreleased
     */
    public function get_item_schema(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/campaign-page',
            'description' => esc_html__('Represents a Campaign Page resource.', 'give'),
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('The Campaign Page ID.', 'give'),
                    'readonly' => true,
                ],
                'campaignId' => [
                    'type' => 'integer',
                    'description' => esc_html__('The associated Campaign ID.', 'give'),
                ],
                'createdAt' => [
                    'type' => ['string', 'null'],
                    'format' => 'date-time',
                    'description' => esc_html__('Creation date-time (Y-m-d H:i:s).', 'give'),
                ],
                'updatedAt' => [
                    'type' => ['string', 'null'],
                    'format' => 'date-time',
                    'description' => esc_html__('Last updated date-time (Y-m-d H:i:s).', 'give'),
                ],
                'status' => [
                    'type' => 'string',
                    'description' => esc_html__('WordPress post status for the page.', 'give'),
                ],
                'content' => [
                    'type' => ['string', 'null'],
                    'description' => esc_html__('Page content.', 'give'),
                ],
            ],
        ];
    }

    // Additional schema helpers may be added here as needed
}



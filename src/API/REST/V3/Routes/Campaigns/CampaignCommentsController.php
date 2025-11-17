<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Donations\ValueObjects\DonationMetaKeys;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class CampaignCommentsController extends WP_REST_Controller
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
     * @since 4.13.0 add schema
     * @since 4.0.0
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . CampaignRoute::CAMPAIGN . '/comments',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_items'],
                    'permission_callback' => '__return_true',
                    'args' => [
                        'id' => [
                            'type' => 'integer',
                            'required' => true,
                            'sanitize_callback' => 'absint',
                        ],
                        'perPage' => [
                            'type' => 'integer',
                            'required' => false,
                            'sanitize_callback' => 'absint',
                        ],
                        'anonymous' => [
                            'type' => 'boolean',
                            'required' => false,
                            'default'  => true,
                        ],
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function get_items($request): WP_REST_Response
    {
        $campaignId = $request->get_param('id');
        $perPage = $request->get_param('perPage');
        $anonymous = $request->get_param('anonymous');

        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            $response = new WP_Error('campaign_not_found', __('Campaign not found', 'give'), ['status' => 404]);

            return rest_ensure_response($response);
        }

        $query = (new CampaignDonationQuery($campaign))
            ->joinDonationMeta(DonationMetaKeys::DONOR_ID, 'donorIdMeta')
            ->joinDonationMeta(DonationMetaKeys::COMMENT, 'commentMeta')
            ->joinDonationMeta(DonationMetaKeys::ANONYMOUS, 'anonymousMeta')
            ->leftJoin('give_donors', 'donorIdMeta.meta_value', 'donors.id', 'donors');

        if (!$anonymous) {
            $query->where('anonymousMeta.meta_value', '1', '!=');
        }

        $query->where('commentMeta.meta_value', '', '!=');
        $query->whereIsNotNull('commentMeta.meta_value');

        $query->select(
            'donorIdMeta.meta_value as donorId',
            'commentMeta.meta_value as comment',
            'anonymousMeta.meta_value as anonymous',
            'donation.post_date as date',
            'donors.name as donorName',
            'donors.email as email'
        );

        $donations = $query->limit($perPage)->getAll();

        $formattedComments = array_map(function ($donation) {
            $donorName = $donation->anonymous === '1' ? __('Anonymous') : $donation->donorName;
            $avatarEmail = $donation->anonymous === '1' ? '' : ($donation->email ?? '');

            return [
                'donorName' => $donorName,
                'comment' => $donation->comment,
                'anonymous' => $donation->anonymous === '1',
                'date' => human_time_diff(strtotime($donation->date)),
                'avatar' => (string) get_avatar_url($avatarEmail),
            ];
        }, $donations);

        $items = new WP_REST_Response($formattedComments);

        return rest_ensure_response($items);
    }

    /**
     * @since 4.13.0
     */
    public function get_item_schema(): array
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'givewp/campaign-comments',
            'description' => esc_html__('Provides comments for a specific campaign.', 'give'),
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => esc_html__('The Campaign ID.', 'give'),
                    'required' => true,
                ],
                'perPage' => [
                    'type' => 'integer',
                    'description' => esc_html__('Comments per page', 'give'),
                ],
                'anonymous' => [
                    'type' => 'boolean',
                    'description' => esc_html__('Include anonymous comments', 'give'),
                ],
            ],
        ];
    }
}

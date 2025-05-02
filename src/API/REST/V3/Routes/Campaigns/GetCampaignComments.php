<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\API\RestRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Donations\ValueObjects\DonationMetaKeys;
use WP_REST_Response;
use WP_REST_Server;

class GetCampaignComments implements RestRoute
{
    /**
     * @since 4.0.0
     */
    public function registerRoute()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/comments',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'handleRequest'],
                    'permission_callback' => '__return_true',
                ],
                'args' => [
                    'id'        => [
                        'type'              => 'integer',
                        'required'          => true,
                        'sanitize_callback' => 'absint',
                    ],
                    'perPage'   => [
                        'type'              => 'integer',
                        'required'          => false,
                        'sanitize_callback' => 'absint',
                    ],
                    'anonymous' => [
                        'type'     => 'boolean',
                        'required' => false,
                        'default'  => true,
                    ],
                ],
            ]
        );
    }

    /**
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function handleRequest($request): WP_REST_Response
    {
        $campaignId = $request->get_param('id');
        $perPage = $request->get_param('perPage');
        $anonymous = $request->get_param('anonymous');

        $campaign = Campaign::find($campaignId);

        if (!$campaign) {
            return new WP_REST_Response('Campaign not found', 404);
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
            'donors.name as donorName'
        );

        $donations = $query->limit($perPage)->getAll();

        $formattedComments = array_map(function ($donation) {
            $donorName = $donation->anonymous === '1' ? __('Anonymous') : $donation->donorName;

            return [
                'donorName' => $donorName,
                'comment'   => $donation->comment,
                'anonymous' => $donation->anonymous === '1',
                'date'      => human_time_diff(strtotime($donation->date)),
                'avatar'    => get_avatar_url($donation->email),
            ];
        }, $donations);

        return new WP_REST_Response($formattedComments);
    }
}

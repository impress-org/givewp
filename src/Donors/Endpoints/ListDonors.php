<?php

namespace Give\Donors\Endpoints;

use Give\Helpers\Date;
use WP_REST_Request;
use WP_REST_Response;

class ListDonors extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/donors';

    /**
     * @inheritDoc
     */
    public function registerRoute()
    {
        register_rest_route(
            'give-api/v2',
            $this->endpoint,
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 1,
                        'minimum' => 1
                    ],
                    'perPage' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 30,
                        'minimum' => 1
                    ],
                    'donations' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'search' => [
                        'type' => 'string',
                        'required' => false
                    ],
                    'start' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                    'form' => [
                        'type' => 'integer',
                        'required' => false,
                        'default' => 0
                    ],
                    'end' => [
                        'type' => 'string',
                        'required' => false,
                        'validate_callback' => [$this, 'validateDate']
                    ],
                ],
            ]
        );
    }

    /**
     * @param WP_REST_Request $request
     * @unreleased
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $data = [];
        $donors = give()->donors->getDonorsForRequest($request);
        $donorsCount = give()->donors->getTotalDonorsCountForRequest($request);
        $pageCount = (int)ceil($donorsCount / $request->get_param('perPage'));

        foreach ($donors as $donor) {
            $donorType = give()->donors->getDonorType($donor->id);
            $donorLatestDonationDate = give()->donors->getDonorLatestDonationDate($donor->id);

            $data[] = [
                'id' => $donor->id,
                'userId' => $donor->userId,
                'email' => $donor->email,
                'name' => $donor->name,
                'titlePrefix' => $donor->titlePrefix,
                'donationCount' => $donor->donationCount,
                'dateCreated' => Date::getDateTime($donor->createdAt),
                'donorType' => $donorType,
                'latestDonation' => $donorLatestDonationDate ? Date::getDateTime($donorLatestDonationDate) : '',
                'donationRevenue' => $this->formatAmount($donor->donationRevenue),
                'gravatar' => give_validate_gravatar($donor->email) ? get_avatar_url($donor->email) : GIVE_PLUGIN_URL . 'assets/dist/images/anonymous-user.svg',
            ];
        }

        return new WP_REST_Response(
            [
                'items' => $data,
                'totalItems' => $donorsCount,
                'totalPages' => $pageCount
            ]
        );
    }

    /**
     * @param string $amount
     * @unreleased
     *
     * @return string
     */
    private function formatAmount($amount)
    {
        return html_entity_decode(give_currency_filter(give_format_amount($amount)));
    }
}

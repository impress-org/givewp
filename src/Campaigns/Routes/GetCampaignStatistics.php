<?php

namespace Give\Campaigns\Routes;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;
use Give\API\RestRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignRoute;
use Give\Framework\Support\Facades\DateTime\Temporal;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class GetCampaignStatistics implements RestRoute
{
    /**
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/statistics',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'handleRequest'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'id' => [
                        'type' => 'integer',
                        'required' => true,
                        'sanitize_callback' => 'absint',
                    ],
                    'rangeInDays' => [
                        'type' => 'integer',
                        'required' => false,
                        'sanitize_callback' => 'absint',
                        'default' => 0, // Zero to mean "all time".
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     *
     * @throws Exception
     */
    public function handleRequest($request): WP_REST_Response
    {
        $campaign = Campaign::find($request->get_param('id'));

        $query = new CampaignDonationQuery($campaign);

        if(!$request->get_param('rangeInDays')) {
            return new WP_REST_Response([[
                'amountRaised' => $query->sumIntendedAmount(),
                'donationCount' => $query->countDonations(),
                'donorCount' => $query->countDonors(),
            ]]);
        }

        $days = $request->get_param('rangeInDays');
        $date = new DateTimeImmutable('now', wp_timezone());
        $interval = DateInterval::createFromDateString("-$days days");
        $period = new DatePeriod($date, $interval, 1);

        return new WP_REST_Response(array_map(function($targetDate) use ($query, $interval) {

            $query = $query->between(
                Temporal::withStartOfDay($targetDate->add($interval)),
                Temporal::withEndOfDay($targetDate)
            );

            return [
                'amountRaised' => $query->sumIntendedAmount(),
                'donationCount' => $query->countDonations(),
                'donorCount' => $query->countDonors(),
            ];
        }, iterator_to_array($period) ));
    }
}

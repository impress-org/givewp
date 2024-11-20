<?php

namespace Give\Campaigns\Routes;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
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
class GetCampaignRevenue implements RestRoute
{
    /**
     * @unreleased
     */
    public function registerRoute()
    {
        register_rest_route(
            CampaignRoute::NAMESPACE,
            CampaignRoute::CAMPAIGN . '/revenue',
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

        $dates = $this->getDatesFromRange(new DateTime('-7 days'), new DateTime());

        $query = new CampaignDonationQuery($campaign);
        $query->between(new DateTime('-7 days'), new DateTime());
        $results = $query->getDonationsByDay();

        foreach($results as $result) {
            $dates[$result->date] = $result->amount;
        }

        $data = [];
        foreach($dates as $date => $amount) {
            $data[] = [
                'date' => $date,
                'amount' => $amount,
            ];
        }

        return new WP_REST_Response($data, 200);
    }

    public function getDatesFromRange(DateTimeInterface $startDate, DateTimeInterface $endDate): array
    {
        $period = new DatePeriod(
            $startDate,
            new DateInterval('P1D'),
            $endDate
        );

        $dates = array_map(function($date) {
            return $date->format('Y-m-d');
        }, iterator_to_array($period));

        return array_fill_keys($dates, 0);
    }
}

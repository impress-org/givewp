<?php

namespace Give\Campaigns\Routes;

use DateInterval;
use DateMalformedPeriodStringException;
use \DatePeriod;
use DateTime;
use \DateTimeInterface;
use Exception;
use Give\API\RestRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Campaigns\ValueObjects\CampaignRoute;
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
     *
     * This request returns revenue for a campaign by day.
     * The result will include all days between either the (campaign start date or oldest result) and the current date.
     *
     * @example
     *
     * $data = [
     * ['date' => '2025-01-01', 'amount' => 100],
     * ['date' => '2025-01-02', 'amount' => 0],
     * ['date' => '2025-01-03', 'amount' => 200],
     * ['date' => '2025-01-04', 'amount' => 0],
     * ];
     *
     * @unreleased
     *
     * @throws Exception
     */
    public function handleRequest($request): WP_REST_Response
    {
        $campaign = Campaign::find($request->get_param('id'));

        if (!$campaign) {
            return new WP_REST_Response([], 404);
        }

        $query = new CampaignDonationQuery($campaign);
        $results = $query->getDonationsByDay();

        if (empty($results)){
            return new WP_REST_Response([], 200);
        }

        $resultMap = [];
        foreach ($results as $result) {
            $resultMap[$result->date] = $result->amount;
        }

        $firstResultDate = new DateTime($results[0]->date);
        $lastResultDate = new DateTime($results[count($results) - 1]->date);

        $queryStartDate = ($firstResultDate < $campaign->startDate) ? $firstResultDate : $campaign->startDate;
        $campaignEndDate = current_datetime();
        $queryEndDate = ($lastResultDate > $campaignEndDate) ? $lastResultDate : $campaignEndDate;

        $dates = $this->getDatesFromRange($queryStartDate, $queryEndDate);

        $data = [];
        foreach($dates as $date) {
            $data[] = [
                'date' => $date,
                'amount' => $resultMap[$date] ?? 0
            ];
        }

        return new WP_REST_Response($data, 200);
    }

    /**
     * @unreleased
     * @throws DateMalformedPeriodStringException
     */
    public function getDatesFromRange(DateTimeInterface $startDate, DateTimeInterface $endDate): array
    {
        // Include the end date
        $endDate->modify('+1 day');

        $startDateInterval = $startDate->diff($endDate);
        if ($startDateInterval->days < 7) {
            $startDate->modify('-7 days');
        }

        $interval = new DateInterval('P1D');
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        $days = [];
        foreach ($dateRange as $date) {
            $days[] = $date->format('Y-m-d');
        }

        return $days;
    }
}

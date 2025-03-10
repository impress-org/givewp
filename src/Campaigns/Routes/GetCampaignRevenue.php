<?php

namespace Give\Campaigns\Routes;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
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

        // the query start date is the earliest of the first result date and the campaign start date
        $queryStartDate = ($firstResultDate < $campaign->startDate) ? $firstResultDate : $campaign->startDate;
        $campaignEndDate = current_datetime();
        // the query end date is the latest of the last result date and the campaign end date
        $queryEndDate = ($lastResultDate > $campaignEndDate) ? $lastResultDate : $campaignEndDate;

        // Get all dates between the start and end date
        $dates = $this->getDatesFromRange($queryStartDate, $queryEndDate);

        $data = [];
        // Fill in the data with the results
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
     */
    public function getDatesFromRange(DateTimeInterface $startDate, DateTimeInterface $endDate): array
    {
        // Include the end date
        $endDate->modify('+1 day');

        $startDateInterval = $startDate->diff($endDate);

        // If the date range is less than 7 days, pad the start date to include the last 7 days
        // This is to ensure that the chart always shows at least 7 days of data
        if ($startDateInterval->days < 7) {
            $defaultDays = 7 - $startDateInterval->days;
            $startDate->modify("-$defaultDays days");
        }

        $intervalTime = '1 day';
        // If the date range is more than 1 year, group by month
        if ($startDateInterval->days >= 365) {
            $intervalTime = '1 months';
        }

        $interval = DateInterval::createFromDateString($intervalTime);
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        $days = [];
        foreach ($dateRange as $date) {
            $days[] = $date->format('Y-m-d');
        }

        return $days;
    }
}

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

        $oldestRevenueDate = $query->getOldestDonationDate();

        if (!$oldestRevenueDate) {
            return new WP_REST_Response([], 200);
        }

        $firstResultDate = new DateTime($oldestRevenueDate, wp_timezone());

        // the query start date is the earliest of the first result date and the campaign start date
        $queryStartDate = ($firstResultDate < $campaign->startDate) ? $firstResultDate : $campaign->startDate;
        $queryEndDate = $campaign->endDate ?: current_datetime();

        $groupBy = $this->getGroupByFromDateRange($queryStartDate, $queryEndDate);

        $results = $query->getDonationsByDate($groupBy);

        if (empty($results)) {
            return new WP_REST_Response([], 200);
        }

        // Map the results by date
        $resultMap = $this->mapResultsByDate($results, $groupBy);

        // Get all dates between the start and end date based on the group by
        $dates = $this->getDatesFromRange($queryStartDate, $queryEndDate, $groupBy);

        // Merge the results with the dates to ensure that all dates are included
        $data = $this->mergeResultsWithDates($dates, $resultMap);

        return new WP_REST_Response($data, 200);
    }

    /**
     * @unreleased
     */
    public function getDatesFromRange(DateTimeInterface $startDate, DateTimeInterface $endDate, string $groupBy): array
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

        $differenceInMonths = ($startDateInterval->y * 12) + $startDateInterval->m;

        $intervalTime = 'days';
        if ($startDateInterval->y >= 5) {
            // If the date range is more than 5 years, group by year
            $intervalTime = 'years';
        } elseif ($differenceInMonths >= 6) {
            // If the date range is more than 6 months, group by month
            $intervalTime = 'months';
        }

        $interval = DateInterval::createFromDateString("1 $intervalTime");
        $dateRange = new DatePeriod($startDate, $interval, $endDate);

        $dates = [];
        foreach ($dateRange as $date) {
            $dateFormatted = $this->getFormattedDateFromGroupBy($groupBy, $date);

            $dates[] = $dateFormatted;
        }

        return $dates;
    }

    /**
     * @unreleased
     */
    public function getGroupByFromDateRange(DateTimeInterface $startDate, DateTimeInterface $endDate): string
    {
        $startDateInterval = $startDate->diff($endDate);
        $differenceInMonths = ($startDateInterval->y * 12) + $startDateInterval->m;

        // If the date range is more than 1 year, group by month
        if ($startDateInterval->y >= 5) {
            return 'YEAR';
        }

        if ($differenceInMonths >= 6) {
            // If the date range is more than 90 days, group by week
            return 'MONTH';
        }

        return 'DAY';
    }

    /**
     * @unreleased
     */
    public function getFormattedDateFromGroupBy(string $groupBy, DateTimeInterface $date): string
    {
        if ($groupBy === 'MONTH') {
            return $date->format('Y-m');
        }

        if ($groupBy === 'YEAR') {
            return $date->format('Y');
        }

        return $date->format('Y-m-d');
    }

    /**
     * @unreleased
     */
    public function mergeResultsWithDates(array $dates, array $resultMap): array
    {
        $data = [];
         // Fill in the data with the results
        foreach ($dates as $date) {
            $data[] = [
                'date' => $date,
                'amount' => $resultMap[$date] ?? 0
            ];
        }

        return $data;
    }

    /**
     * @unreleased
     */
    public function mapResultsByDate(array $results, string $groupBy): array
    {
        $resultMap = [];
        foreach ($results as $result) {
            $date = $this->getFormattedDateFromGroupBy($groupBy, date_create($result->date_created));

            $resultMap[$date] = $result->amount;
        }

        return $resultMap;
    }
}

<?php

namespace Give\API\REST\V3\Routes\Campaigns;

use DateInterval;
use DatePeriod;
use DateTime;
use DateTimeInterface;
use Exception;
use Give\API\REST\V3\Routes\Campaigns\ValueObjects\CampaignRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Server;

/**
 * @unreleased
 */
class CampaignRevenueController extends WP_REST_Controller
{
    /**
     * @var string
     */
    protected $namespace;

    /**
     * @unreleased
     */
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
            '/' . CampaignRoute::CAMPAIGN . '/revenue',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_items'],
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                    'args' => [
                        'id' => [
                            'type' => 'integer',
                            'required' => true,
                            'sanitize_callback' => 'absint',
                        ],
                    ],
                ],
                'schema' => [$this, 'get_public_item_schema'],
            ]
        );
    }

    /**
     * @unreleased
     * @throws Exception
     */
    public function get_items($request): WP_REST_Response
    {
        $campaign = Campaign::find((int)$request->get_param('id'));

        if (!$campaign) {
            $response = new WP_REST_Response(__('Campaign not found', 'give'), 404);

            return rest_ensure_response($response);
        }

        $query = new CampaignDonationQuery($campaign);

        $oldestRevenueDate = $query->getOldestDonationDate();

        if (!$oldestRevenueDate) {
            $items = new WP_REST_Response([]);

            return rest_ensure_response($items);
        }

        $firstResultDate = new DateTime($oldestRevenueDate, wp_timezone());

        // the query start date is the earliest of the first result date and the campaign start date
        $queryStartDate = ($firstResultDate < $campaign->startDate) ? $firstResultDate : $campaign->startDate;
        $queryEndDate = $campaign->endDate ?: current_datetime();

        $groupBy = $this->getGroupByFromDateRange($queryStartDate, $queryEndDate);

        $results = $query->getDonationsByDate($groupBy);

        if (empty($results)) {
            $items = new WP_REST_Response([]);

            return rest_ensure_response($items);
        }

        // Map the results by date
        $resultMap = $this->mapResultsByDate($results, $groupBy);

        // Get all dates between the start and end date based on the group by
        $endTimestamp = strtotime($queryEndDate->format('Y-m-d H:i:s') . ' +1 day');
        $queryEndDatePlusOne = new DateTime(date('Y-m-d H:i:s', $endTimestamp), wp_timezone());
        $dates = $this->getDatesFromRange($queryStartDate, $queryEndDatePlusOne, $groupBy);

        // Merge the results with the dates to ensure that all dates are included
        $data = $this->mergeResultsWithDates($dates, $resultMap);

        $items = new WP_REST_Response($data);

        return rest_ensure_response($items);
    }

    /**
     * @unreleased
     */
    public function getDatesFromRange(DateTimeInterface $startDate, DateTimeInterface $endDate, string $groupBy): array
    {
        $startDateInterval = $startDate->diff($endDate);

        // If the date range is less than 7 days, pad the start date to include the last 7 days
        // This is to ensure that the chart always shows at least 7 days of data
        $start = new DateTime($startDate->format('Y-m-d H:i:s'), wp_timezone());
        if ($startDateInterval->days < 7) {
            $defaultDays = 7 - $startDateInterval->days;
            $start = new DateTime(date('Y-m-d H:i:s', strtotime($start->format('Y-m-d H:i:s') . " -$defaultDays days")), wp_timezone());
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
        $dateRange = new DatePeriod($start, $interval, $endDate);

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

            $resultMap[$date] = (float)$result->amount;
        }

        return $resultMap;
    }

    /**
     * @unreleased
     */
    public function get_item_schema(): array
    {
        return [
            'title'   => 'campaign-revenue',
            'description' => esc_html__('Provides daily revenue data for a specific campaign.', 'give'),
            'type' => 'array',
            'readonly' => true,
            'items' => [
                'type' => 'object',
                'properties' => [
                    'date' => [
                        'type' => 'string',
                        'format' => 'date',
                        'description' => esc_html__('The date for the revenue entry (YYYY-MM-DD).', 'give'),
                    ],
                    'amount' => [
                        'type' => ['integer', 'number'],
                        'description' => esc_html__('The amount of revenue received on the given date.', 'give'),
                    ],
                ]
            ]
        ];
    }
}

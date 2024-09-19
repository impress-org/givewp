<?php

namespace Give\Campaigns\Routes;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Give\API\RestRoute;
use Give\Campaigns\CampaignDonationQuery;
use Give\Campaigns\Models\Campaign;
use Give\Framework\Support\Facades\DateTime\Temporal;

class CampaignOverviewStatistics implements RestRoute
{
    /** @var string */
    protected $endpoint = 'campaign-overview-statistics';

    /**
     * @unreleased
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
                    'permission_callback' => function () {
                        return current_user_can('manage_options');
                    },
                ],
                'args' => [
                    'campaignId' => [
                        'type' => 'integer',
                        'required' => true,
                        'validate_callback' => 'is_numeric',
                    ],
                    'rangeInDays' => [
                        'type' => 'integer',
                        'required' => false,
                        'validate_callback' => 'is_numeric',
                        'default' => 0, // Zero to mean "all time".
                    ],
                ],
            ]
        );
    }

    /**
     * @unreleased
     */
    public function handleRequest($request)
    {
        $campaign = Campaign::find($request->get_param('campaignId'));

        $query = new CampaignDonationQuery($campaign);

        if(!$request->get_param('rangeInDays')) {
            return [[
                'amountRaised' => $query->sumIntendedAmount(),
                'donationCount' => $query->countDonations(),
                'donorCount' => $query->countDonors(),
            ]];
        }

        $days = $request->get_param('rangeInDays');
        $date = new DateTimeImmutable('now', wp_timezone());
        $interval = DateInterval::createFromDateString("-$days days");
        $period = new DatePeriod($date, $interval, 1);

        return array_map(function($targetDate) use ($query, $interval) {

            $query = $query->between(
                Temporal::withStartOfDay($targetDate->add($interval)),
                Temporal::withEndOfDay($targetDate)
            );

            return [
                'amountRaised' => $query->sumIntendedAmount(),
                'donationCount' => $query->countDonations(),
                'donorCount' => $query->countDonors(),
            ];
        }, iterator_to_array($period) );
    }
}

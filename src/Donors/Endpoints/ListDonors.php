<?php

namespace Give\Donors\Endpoints;

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
        $donors = give()->donorRepository->getDonorsForRequest($request);
        $donorsCount = give()->donorRepository->getTotalDonorsCountForRequest($request);
        $pageCount = (int)ceil($donorsCount / $request->get_param('perPage'));

        foreach ($donors as $donor) {
            $data[] = [
                'id' => $donor->id,
                'userId' => $donor->userId,
                'email' => $donor->email,
                'name' => $donor->name,
                'titlePrefix' => $donor->titlePrefix,
                'donationCount' => $donor->donationCount,
                'dateCreated' => $this->getDateTime($donor->createdAt),
                'donationRevenue' => $this->formatAmount($donor->donationRevenue),
                'hasGravatar' => give_validate_gravatar($donor->email)
            ];
        }

        return new WP_REST_Response(
            [
                'donors' => $data,
                'donorsCount' => $donorsCount,
                'pageCount' => $pageCount
            ]
        );
    }


    /**
     * Returns human readable date.
     *
     * @param string $date Date YYYY-MM-DD
     *
     * @return string
     */
    private function getDateTime($date)
    {
        $dateTimestamp = strtotime($date);
        $currentTimestamp = current_time('timestamp');
        $todayTimestamp = strtotime('today', $currentTimestamp);
        $yesterdayTimestamp = strtotime('yesterday', $currentTimestamp);

        if ($dateTimestamp >= $todayTimestamp) {
            return sprintf(
                '%1$s %2$s %3$s',
                esc_html__('Today', 'give'),
                esc_html__('at', 'give'),
                date_i18n(get_option('time_format'), $dateTimestamp)
            );
        }

        if ($dateTimestamp >= $yesterdayTimestamp) {
            return sprintf(
                '%1$s %2$s %3$s',
                esc_html__('Yesterday', 'give'),
                esc_html__('at', 'give'),
                date_i18n(get_option('time_format'), $dateTimestamp)
            );
        }

        return date_i18n(get_option('date_format'), $dateTimestamp);
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

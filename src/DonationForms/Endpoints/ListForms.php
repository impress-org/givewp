<?php

namespace Give\DonationForms\Endpoints;

use WP_REST_Request;
use WP_REST_Response;

/**
 * @since 2.19.0
 */
class ListForms extends Endpoint
{
    /**
     * @var string
     */
    protected $endpoint = 'admin/forms';

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
                    'methods'             => 'GET',
                    'callback'            => [$this, 'handleRequest'],
                    'permission_callback' => [$this, 'permissionsCheck'],
                ],
                'args' => [
                    'page'    => [
                        'type'              => 'integer',
                        'required'          => false,
                        'default'           => 1,
                        'minimum'           => 1
                    ],
                    'perPage' => [
                        'type'              => 'integer',
                        'required'          => false,
                        'default'           => 30,
                        'minimum'           => 1
                    ],
                    'status'  => [
                        'type'              => 'string',
                        'required'          => false,
                        'default'           => 'any',
                        'enum'              => [
                            'publish',
                            'future',
                            'draft',
                            'pending',
                            'trash',
                            'auto-draft',
                            'inherit',
                            'any'
                        ]
                    ],
                    'search'  => [
                        'type'              => 'string',
                        'required'          => false
                    ]
                ],
            ]
        );
    }

    /**
     * @param  WP_REST_Request  $request
     *
     * @return WP_REST_Response
     */
    public function handleRequest(WP_REST_Request $request)
    {
        $data  = [];
        $forms = give()->donationFormsRepository->getFormsForRequest($request);
        $totalForms = give()->donationFormsRepository->getTotalFormsCountForRequest($request);
        $totalPages = (int)ceil($totalForms / $request->get_param('perPage'));

        foreach ($forms as $form) {
            $data[] = [
                'id'        => $form->id,
                'name'      => $form->title,
                'status'    => $form->status,
                'goal'      => $form->goalEnabled === 'enabled' ? $this->getGoal($form->id) : false,
                'donations' => give()->donationFormsRepository->getFormDonationsCount($form->id),
                'amount'    => $this->getFormAmount($form),
                'revenue'   => $this->formatAmount($form->revenue),
                'datetime'  => $this->getDateTime($form->createdAt),
                'shortcode' => sprintf('[give_form id="%d"]', $form->id),
                'permalink' => html_entity_decode(get_permalink($form->id)),
                'edit'      => html_entity_decode(get_edit_post_link($form->id))
            ];
        }

        return new WP_REST_Response(
            [
                'forms'      => $data,
                'totalForms' => $totalForms,
                'totalPages' => $totalPages
            ]
        );
    }

    /**
     * @param  int  $formId
     *
     * @return array
     */
    private function getGoal($formId)
    {
        $goal = give_goal_progress_stats($formId);

        $getFormatFromGoal = function ($goal) {
            switch ($goal[ 'format' ]) {
                case 'donation':
                    return _n('donation', 'donations', $goal[ 'raw_goal' ], 'give');

                case 'donors':
                    return _n('donor', 'donors', $goal[ 'raw_goal' ], 'give');

                case 'amount':
                    return __('amount', 'give');

                case 'percentage':
                    return __('percentage', 'give');

                default:
                    return '';
            }
        };

        return [
            'actual'   => html_entity_decode($goal[ 'actual' ]),
            'goal'     => html_entity_decode($goal[ 'goal' ]),
            'progress' => html_entity_decode($goal[ 'progress' ]),
            'format'   => $getFormatFromGoal($goal)
        ];
    }

    /**
     * Returns human readable date.
     *
     * @param  string  $date Date in mysql format.
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

        if ($dateTimestamp < $todayTimestamp && $dateTimestamp >= $yesterdayTimestamp) {
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
     * @param  object  $form
     *
     *
     * @return string
     */
    private function getFormAmount($form)
    {
        $donationLevels = unserialize($form->donationLevels);

        if (is_array($donationLevels)) {
            $amount = array_column($donationLevels, '_give_amount');

            return $this->formatAmount(min($amount)) . ' - ' . $this->formatAmount(max($amount));
        }

        return $this->formatAmount($form->setPrice);
    }

    /**
     * @param  string  $amount
     *
     * @return string
     */
    private function formatAmount($amount)
    {
        return html_entity_decode(give_currency_filter(give_format_amount($amount)));
    }
}

<?php

namespace Give\DonationForms\Endpoints;

use Give\Framework\Database\DB;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @unreleased
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
                        'type'              => 'int',
                        'required'          => false,
                        'validate_callback' => [$this, 'validateInt'],
                        'default'           => 1
                    ],
                    'perPage' => [
                        'type'              => 'int',
                        'required'          => false,
                        'validate_callback' => [$this, 'validateInt'],
                        'default'           => 30
                    ],
                    'status'  => [
                        'type'              => 'string',
                        'required'          => false,
                        'validate_callback' => [$this, 'validateStatus'],
                        'default'           => 'any'
                    ],
                    'search'  => [
                        'type'              => 'string',
                        'required'          => false,
                        'sanitize_callback' => [$this, 'sanitizeString']
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
        $total = give()->donationFormsRepository->getTotalFormsCountForRequest($request);

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
                'totalPages' => $total,
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
     * @param  string  $date
     *
     * @return string
     */
    private function getDateTime($date)
    {
        $date      = date_create($date);
        $timestamp = $date->getTimestamp();
        $time      = date_i18n(get_option('time_format'), $timestamp);

        if ($timestamp >= strtotime('today')) {
            return __('Today', 'give') . ' ' . __('at', 'give') . ' ' . $time;
        }

        if ($timestamp >= strtotime('yesterday')) {
            return __('Yesterday', 'give') . ' ' . __('at', 'give') . ' ' . $time;
        }

        return date_i18n(get_option('date_format'), $timestamp);
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

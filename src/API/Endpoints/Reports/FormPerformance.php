<?php

/**
 * Form Performance endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class FormPerformance extends Endpoint
{

    protected $payments;

    public function __construct()
    {
        $this->endpoint = 'form-performance';
    }

    public function getReport($request)
    {
        $start = date_create($request->get_param('start'));
        $end = date_create($request->get_param('end'));
        $diff = date_diff($start, $end);

        $data = $this->get_data($start, $end);

        return $data;
    }

    public function get_data($start, $end)
    {
        $paymentObjects = $this->getPayments($start->format('Y-m-d'), $end->format('Y-m-d'), 'date', -1);

        $forms = [];
        $labels = [];
        $tooltips = [];

        if (count($paymentObjects) > 0) {
            foreach ($paymentObjects as $paymentObject) {
                if ($paymentObject->status === 'publish' || $paymentObject->status === 'give_subscription') {
                    $forms[$paymentObject->form_id]['income'] = isset($forms[$paymentObject->form_id]['income']) ? $forms[$paymentObject->form_id]['income'] += $paymentObject->total : $paymentObject->total;
                    $forms[$paymentObject->form_id]['donations'] = isset($forms[$paymentObject->form_id]['donations']) ? $forms[$paymentObject->form_id]['donations'] += 1 : 1;
                    $forms[$paymentObject->form_id]['title'] = $paymentObject->form_title;
                }
            }

            $sorted = usort(
                $forms,
                function ($a, $b) {
                    if ($a['income'] == $b['income']) {
                        return 0;
                    }

                    return ($a['income'] > $b['income']) ? -1 : 1;
                }
            );

            if ($sorted === true) {
                $forms = array_slice($forms, 0, 5);

                foreach ($forms as $key => $value) {
                    $tooltips[] = [
                        'title' => give_currency_filter(
                            give_format_amount($value['income']),
                            [
                                'currency_code' => $this->currency,
                                'decode_currency' => true,
                                'sanitize' => false,
                            ]
                        ),
                        'body' => $value['donations'] . ' ' . __('Donations', 'give'),
                        'footer' => $value['title'],
                    ];
                    $labels[] = $value['title'];
                    $forms[$key] = $value['income'];
                }

                $forms = array_values($forms);
            }
        } else {
            $formsQuery = new \Give_Forms_Query(['posts_per_page' => 5]);

            $allForms = $formsQuery->get_forms();

            foreach ($allForms as $form) {
                $forms[$form->ID]['income'] = 0;
                $forms[$form->ID]['donations'] = 0;
                $forms[$form->ID]['title'] = $form->post_title;
            }

            foreach ($forms as $key => $value) {
                $tooltips[] = [
                    'title' => give_currency_filter(
                        give_format_amount($value['income']),
                        [
                            'currency_code' => $this->currency,
                            'decode_currency' => true,
                            'sanitize' => false,
                        ]
                    ),
                    'body' => $value['donations'] . ' ' . __('Donations', 'give'),
                    'footer' => $value['title'],
                ];
                $labels[] = $value['title'];
                $forms[$key] = $value['income'];
            }

            $forms = array_values($forms);
        }

        // Create data object to be returned, with 'highlights' object containing total and average figures to display
        return [
            'datasets' => [
                [
                    'data' => $forms,
                    'tooltips' => $tooltips,
                    'labels' => $labels,
                ],
            ],
        ];
    }
}

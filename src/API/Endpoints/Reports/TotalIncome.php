<?php

/**
 * Income over time endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalIncome extends Endpoint
{

    protected $payments;

    public function __construct()
    {
        $this->endpoint = 'total-income';
    }

    public function getReport($request)
    {
        $start = date_create($request->get_param('start'));
        $end = date_create($request->get_param('end'));
        $diff = date_diff($start, $end);

        $data = [];

        switch (true) {
            case ($diff->days > 12):
                $interval = round($diff->days / 12);
                $data = $this->get_data($start, $end, 'P' . $interval . 'D');
                break;
            case ($diff->days > 5):
                $data = $this->get_data($start, $end, 'P1D');
                break;
            case ($diff->days > 4):
                $data = $this->get_data($start, $end, 'PT12H');
                break;
            case ($diff->days > 2):
                $data = $this->get_data($start, $end, 'PT3H');
                break;
            case ($diff->days >= 0):
                $data = $this->get_data($start, $end, 'PT1H');
                break;
        }

        return $data;
    }

    public function get_data($start, $end, $intervalStr)
    {
        $tooltips = [];
        $income = [];

        $interval = new \DateInterval($intervalStr);

        $periodStart = clone $start;
        $periodEnd = clone $start;

        // Subtract interval to set up period start
        date_sub($periodStart, $interval);

        while ($periodStart < $end) {
            $incomeForPeriod = $this->get_income(
                $periodStart->format('Y-m-d H:i:s'),
                $periodEnd->format('Y-m-d H:i:s')
            );
            $time = $periodEnd->format('Y-m-d H:i:s');

            switch ($intervalStr) {
                case 'P1D':
                    $time = $periodStart->format('Y-m-d');
                    $periodLabel = $periodStart->format('l');
                    break;
                case 'PT12H':
                case 'PT3H':
                case 'PT1H':
                    $periodLabel = $periodStart->format('D ga') . ' - ' . $periodEnd->format('D ga');
                    break;
                default:
                    $periodLabel = $periodStart->format('M j, Y') . ' - ' . $periodEnd->format('M j, Y');
            }

            $income[] = [
                'x' => $time,
                'y' => $incomeForPeriod,
            ];

            $tooltips[] = [
                'title' => give_currency_filter(
                    give_format_amount($incomeForPeriod),
                    [
                        'currency_code' => $this->currency,
                        'decode_currency' => true,
                        'sanitize' => false,
                    ]
                ),
                'body' => __('Total Revenue', 'give'),
                'footer' => $periodLabel,
            ];

            // Add interval to set up next period
            date_add($periodStart, $interval);
            date_add($periodEnd, $interval);
        }

        if ($intervalStr === 'P1D') {
            $income = array_slice($income, 1);
            $tooltips = array_slice($tooltips, 1);
        }

        $totalIncomeForPeriod = $this->get_income($start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        $trend = $this->get_trend($start, $end, $income);

        $diff = date_diff($start, $end);
        $info = $diff->days > 1 ? __('VS previous', 'give') . ' ' . $diff->days . ' ' . __('days', 'give') : __(
            'VS previous day',
            'give'
        );

        // Create data object to be returned, with 'highlights' object containing total and average figures to display
        $data = [
            'datasets' => [
                [
                    'data' => $income,
                    'tooltips' => $tooltips,
                    'trend' => $trend,
                    'info' => $info,
                    'highlight' => give_currency_filter(
                        give_format_amount($totalIncomeForPeriod),
                        [
                            'currency_code' => $this->currency,
                            'decode_currency' => true,
                            'sanitize' => false,
                        ]
                    ),
                ],
            ],
        ];

        return $data;
    }

    public function get_trend($start, $end, $income)
    {
        $interval = $start->diff($end);

        $prevStart = clone $start;
        $prevStart = date_sub($prevStart, $interval);

        $prevEnd = clone $start;

        $prevIncome = $this->get_income($prevStart->format('Y-m-d'), $prevEnd->format('Y-m-d'));
        $currentIncome = $this->get_income($start->format('Y-m-d'), $end->format('Y-m-d'));

        // Set default trend to 0
        $trend = 0;

        // Check that prev value and current value are > 0 (can't divide by 0)
        if ($prevIncome > 0 && $currentIncome > 0) {
            // Check if it is a percent decrease, or increase
            if ($prevIncome > $currentIncome) {
                // Calculate a percent decrease
                $trend = ((($prevIncome - $currentIncome) / $prevIncome) * 100) * -1;
            } elseif ($currentIncome > $prevIncome) {
                // Calculate a percent increase
                $trend = (($currentIncome - $prevIncome) / $prevIncome) * 100;
            }
        }

        return $trend;
    }

    public function get_income($startStr, $endStr)
    {
        $paymentObjects = $this->getPayments($startStr, $endStr);

        $income = 0;

        foreach ($paymentObjects as $paymentObject) {
            if ($paymentObject->currency === $this->currency) {
                if ($paymentObject->date >= $startStr && $paymentObject->date < $endStr) {
                    if ($paymentObject->status === 'publish' || $paymentObject->status === 'give_subscription') {
                        $income += $paymentObject->total;
                    }
                }
            }
        }

        return $income;
    }

}

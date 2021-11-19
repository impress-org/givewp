<?php

/**
 * Total donors endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TotalDonors extends Endpoint
{

    protected $payments;

    public function __construct()
    {
        $this->endpoint = 'total-donors';
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
        $donors = [];

        $interval = new \DateInterval($intervalStr);

        $periodStart = clone $start;
        $periodEnd = clone $start;

        // Subtract interval to set up period start
        date_sub($periodStart, $interval);

        while ($periodStart < $end) {
            $donorsForPeriod = $this->get_donors(
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

            $donors[] = [
                'x' => $time,
                'y' => $donorsForPeriod,
            ];

            $tooltips[] = [
                'title' => sprintf(_n('%d Donor', '%d Donors', $donorsForPeriod, 'give'), $donorsForPeriod),
                'body' => __('Total Donors', 'give'),
                'footer' => $periodLabel,
            ];

            // Add interval to set up next period
            date_add($periodStart, $interval);
            date_add($periodEnd, $interval);
        }

        if ($intervalStr === 'P1D') {
            $donors = array_slice($donors, 1);
            $tooltips = array_slice($tooltips, 1);
        }

        $totalDonorsForPeriod = $this->get_donors($start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));
        $trend = $this->get_trend($start, $end, $donors);

        $diff = date_diff($start, $end);
        $info = $diff->days > 1 ? __('VS previous', 'give') . ' ' . $diff->days . ' ' . __('days', 'give') : __(
            'VS previous day',
            'give'
        );

        // Create data objec to be returned, with 'highlights' object containing total and average figures to display
        $data = [
            'datasets' => [
                [
                    'data' => $donors,
                    'tooltips' => $tooltips,
                    'trend' => $trend,
                    'info' => $info,
                    'highlight' => $totalDonorsForPeriod,
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

        $prevDonors = $this->get_donors($prevStart->format('Y-m-d H:i:s'), $prevEnd->format('Y-m-d H:i:s'));
        $currentDonors = $this->get_donors($start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));

        // Set default trend to 0
        $trend = 0;

        // Check that prev value and current value are > 0 (can't divide by 0)
        if ($prevDonors > 0 && $currentDonors > 0) {
            // Check if it is a percent decreate, or increase
            if ($prevDonors > $currentDonors) {
                // Calculate a percent decrease
                $trend = ((($prevDonors - $currentDonors) / $prevDonors) * 100) * -1;
            } elseif ($currentDonors > $prevDonors) {
                // Calculate a percent increase
                $trend = (($currentDonors - $prevDonors) / $prevDonors) * 100;
            }
        }

        return $trend;
    }

    public function get_donors($startStr, $endStr)
    {
        $paymentObjects = $this->getPayments($startStr, $endStr);

        $donors = [];

        foreach ($paymentObjects as $paymentObject) {
            if ($paymentObject->date >= $startStr && $paymentObject->date < $endStr) {
                if ($paymentObject->status == 'publish' || $paymentObject->status == 'give_subscription') {
                    $donors[] = $paymentObject->donor_id;
                }
            }
        }

        $unique = array_unique($donors);
        $donorCount = count($unique);

        return $donorCount;
    }
}

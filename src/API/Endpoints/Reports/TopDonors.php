<?php

/**
 * Top Donors endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class TopDonors extends Endpoint
{

    public function __construct()
    {
        $this->endpoint = 'top-donors';
    }

    public function getReport($request)
    {
        $start = date_create($request->get_param('start'));
        $end = date_create($request->get_param('end'));

        return $this->get_data($start, $end);
    }

    public function get_data($start, $end)
    {
        $paymentObjects = $this->getPayments($start->format('Y-m-d'), $end->format('Y-m-d 23:i:s'), 'date', -1);

        $donors = [];

        foreach ($paymentObjects as $paymentObject) {
            if ($paymentObject->status === 'publish' || $paymentObject->status === 'give_subscription') {
                $donors[$paymentObject->donor_id]['type'] = 'donor';
                $donors[$paymentObject->donor_id]['earnings'] = isset($donors[$paymentObject->donor_id]['earnings']) ? $donors[$paymentObject->donor_id]['earnings'] += $paymentObject->total : $paymentObject->total;
                $donors[$paymentObject->donor_id]['total'] = give_currency_filter(
                    give_format_amount($donors[$paymentObject->donor_id]['earnings'], ['sanitize' => false]),
                    [
                        'currency_code' => $this->currency,
                        'decode_currency' => true,
                        'sanitize' => false,
                    ]
                );
                $donors[$paymentObject->donor_id]['donations'] = isset($donors[$paymentObject->donor_id]['donations']) ? $donors[$paymentObject->donor_id]['donations'] += 1 : 1;
                $countLabel = _n('Donation', 'Donations', $donors[$paymentObject->donor_id]['donations'], 'give');
                $donors[$paymentObject->donor_id]['count'] = $donors[$paymentObject->donor_id]['donations'] . ' ' . $countLabel;
                $donors[$paymentObject->donor_id]['name'] = $paymentObject->first_name . ' ' . $paymentObject->last_name;
                $donors[$paymentObject->donor_id]['email'] = $paymentObject->email;
                $donors[$paymentObject->donor_id]['image'] = give_validate_gravatar(
                    $paymentObject->email
                ) ? get_avatar_url($paymentObject->email, 60) : null;
                $donors[$paymentObject->donor_id]['url'] = admin_url(
                    'edit.php?post_type=give_forms&page=give-donors&view=overview&id=' . absint(
                        $paymentObject->donor_id
                    )
                );
            }
        }

        $sorted = usort(
            $donors,
            function ($a, $b) {
                if ($a['earnings'] == $b['earnings']) {
                    return 0;
                }

                return ($a['earnings'] > $b['earnings']) ? -1 : 1;
            }
        );

        if ($sorted === true) {
            $donors = array_slice($donors, 0, 25);
            $donors = array_values($donors);
        }

        return $donors;
    }

}

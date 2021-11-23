<?php

/**
 * Reports base endpoint
 *
 * @package Give
 */

namespace Give\API\Endpoints\Reports;

class PaymentMethods extends Endpoint
{

    public function __construct()
    {
        $this->endpoint = 'payment-methods';
    }

    public function getReport($request)
    {
        $paymentObjects = $this->getPayments($request->get_param('start'), $request->get_param('end'), 'date', -1);
        $gatewayObjects = give_get_payment_gateways();

        if ($this->testMode === false) {
            unset($gatewayObjects['manual']);
        }

        $gateways = [];
        foreach ($gatewayObjects as $gatewayId => $gatewayObject) {
            $gateways[$gatewayId] = [
                'label' => $gatewayObject['admin_label'],
                'count' => 0,
                'amount' => 0,
            ];
        }

        if (count($paymentObjects) > 0) {
            foreach ($paymentObjects as $paymentObject) {
                $gateways[$paymentObject->gateway]['count'] += 1;
                $gateways[$paymentObject->gateway]['amount'] += $paymentObject->total;
            }
        }

        $gatewaysSorted = usort(
            $gateways,
            function ($a, $b) {
                if ($a['amount'] == $b['amount']) {
                    return 0;
                }

                return ($a['amount'] > $b['amount']) ? -1 : 1;
            }
        );

        $data = [];
        $labels = [];
        $tooltips = [];

        if ($gatewaysSorted == true) {
            $gateways = array_slice($gateways, 0, 5);
            foreach ($gateways as $gateway) {
                $labels[] = $gateway['label'];
                $data[] = $gateway['amount'];
                $tooltips[] = [
                    'title' => give_currency_filter(
                        give_format_amount($gateway['amount']),
                        [
                            'currency_code' => $this->currency,
                            'decode_currency' => true,
                            'sanitize' => false,
                        ]
                    ),
                    'body' => $gateway['count'] . ' ' . __('Payments', 'give'),
                    'footer' => $gateway['label'],
                ];
            }
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'tooltips' => $tooltips,
                ],
            ],
        ];
    }
}

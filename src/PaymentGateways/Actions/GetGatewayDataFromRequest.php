<?php

namespace Give\PaymentGateways\Actions;

/**
 * @unreleased
 */
class GetGatewayDataFromRequest
{
    /**
     * @unreleased
     */
    public function __invoke(): array
    {
        $gatewayData = [];

        if ($requestData = file_get_contents('php://input')) {
            // In Next Gen, we are posting data to the server as application/json not formData like wp-ajax does,
            // So we need get gateway data from request body.
            // Gateway data will be accessible in createPayment or createSubscription function of gateway class .
            $requestData = json_decode($requestData, true);
            if (array_key_exists('gatewayData', $requestData)) {
                $gatewayData = give_clean($requestData['gatewayData']);
            }
        } elseif (!empty($_REQUEST['gatewayData'])) {
            // Gateway api will automatically get data from request if present under `gatewayData` key name.
            // Gateway data will be accessible in createPayment or createSubscription function of gateway class .
            $gatewayData = give_clean($_REQUEST['gatewayData']);
        }

        return $gatewayData;
    }
}

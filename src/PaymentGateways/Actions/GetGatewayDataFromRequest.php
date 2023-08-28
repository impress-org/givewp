<?php

namespace Give\PaymentGateways\Actions;

/**
 * @since 2.22.0
 */
class GetGatewayDataFromRequest
{
    /**
     * This filter logic will support the request coming in as application/json or formData.
     * In order for the $gatewayData to be automatically accessible the data will need to come in
     * through a specific key called `gatewayData`.
     *
     * @since 3.0.0 Updated logic to support all native content types.
     * @since 2.22.0
     */
    public function __invoke(): array
    {
        $gatewayData = [];

        if (isset($_REQUEST['gatewayData'])) {
            $gatewayData = give_clean($_REQUEST['gatewayData']);
        } else if ($this->requestIsJson()) {
            $requestData = file_get_contents('php://input');
            $requestData = json_decode($requestData, true);

            if (array_key_exists('gatewayData', $requestData)) {
                $gatewayData = give_clean($requestData['gatewayData']);
            }
        }

        return $gatewayData;
    }

     /**
     * This checks the server content type for 'application/json' to determine if it is a json request.
     *
     * @since 3.0.0
     */
    protected function requestIsJson(): bool
    {
        return isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
    }
}

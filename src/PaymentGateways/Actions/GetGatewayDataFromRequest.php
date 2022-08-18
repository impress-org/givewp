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
     * @since 2.22.0
     */
    public function __invoke(): array
    {
        $gatewayData = [];

        if (!isset($_SERVER['CONTENT_TYPE'])) {
            return $gatewayData;
        }

        $contentType = $_SERVER['CONTENT_TYPE'];

        // this content type is typically used throughout legacy with jQuery and wp-ajax
        if (($contentType === "application/x-www-form-urlencoded") && isset($_REQUEST['gatewayData'])) {
            $gatewayData = give_clean($_REQUEST['gatewayData']);
        }

        // this content type is typically used with the fetch api and our custom routes
        if ($contentType === "application/json") {
            $requestData = file_get_contents('php://input');
            $requestData = json_decode($requestData, true);

            if (array_key_exists('gatewayData', $requestData)) {
                $gatewayData = give_clean($requestData['gatewayData']);
            }
        }

        return $gatewayData;
    }
}

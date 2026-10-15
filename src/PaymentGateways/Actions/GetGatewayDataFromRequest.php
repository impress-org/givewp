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

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- donation request; DonateRoute verifies the signed route for v3 forms and give_process_donation() verifies the donation form nonce for v2 forms.; give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data.
        if (isset($_REQUEST['gatewayData'])) {
            $gatewayData = give_clean($_REQUEST['gatewayData']); // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- same donation request as above; give_clean() unslashes and sanitizes with sanitize_text_field(), and drops serialized data.
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
     * @since TBD Unslash and sanitize the content type.
     * @since 3.0.0
     */
    protected function requestIsJson(): bool
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? sanitize_text_field(wp_unslash($_SERVER['CONTENT_TYPE'])) : '';

        return str_contains($contentType, 'application/json');
    }
}

<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Traits;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;

/**
 * Shared checks for the webhook endpoint actions.
 *
 * @since TBD
 */
trait PreparesWebhookEndpointRequests
{
    /**
     * Returns the Stripe request options that authenticate as the connected account.
     *
     * For accounts connected through GiveWP Connect the stored "secret key" is the OAuth access token,
     * so endpoints created with it belong to the connected account itself.
     *
     * @since TBD
     *
     * @param string $mode "live" or "test"
     *
     * @throws WebhookEndpointException
     */
    private function requestOptions(AccountDetail $account, string $mode): array
    {
        if (!in_array($mode, ['live', 'test'], true)) {
            throw WebhookEndpointException::invalidMode($mode);
        }

        $token = 'live' === $mode ? $account->liveSecretKey : $account->testSecretKey;
        $otherModeMarker = 'live' === $mode ? '_test_' : '_live_';

        if (!$token || false !== strpos($token, $otherModeMarker)) {
            throw WebhookEndpointException::missingAccessToken($mode);
        }

        return ['api_key' => $token];
    }

    /**
     * @since TBD
     *
     * @throws WebhookEndpointException
     */
    private function assertEndpointId(string $endpointId): void
    {
        if (!preg_match('/^we_[A-Za-z0-9]+$/', $endpointId)) {
            throw WebhookEndpointException::invalidEndpointId($endpointId);
        }
    }
}

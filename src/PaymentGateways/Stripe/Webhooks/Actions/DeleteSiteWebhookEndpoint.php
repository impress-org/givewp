<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\WebhookLogger;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Throwable;

/**
 * Deletes the webhook endpoints that deliver to this site from a Stripe account that is being disconnected.
 *
 * It never throws. A Stripe failure, such as a revoked OAuth token, is logged and reported in the result so
 * that the account can still be removed from GiveWP.
 *
 * @since TBD
 */
final class DeleteSiteWebhookEndpoint
{
    public const DELETED = 'deleted';
    public const NOTHING_MATCHED = 'nothing_matched';
    public const FAILED = 'failed';

    /**
     * @var ListWebhookEndpoints
     */
    private $listEndpoints;

    /**
     * @var DeleteWebhookEndpoint
     */
    private $deleteEndpoint;

    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since TBD
     */
    public function __construct(
        ListWebhookEndpoints $listEndpoints,
        DeleteWebhookEndpoint $deleteEndpoint,
        WebhookTarget $target
    ) {
        $this->listEndpoints = $listEndpoints;
        $this->deleteEndpoint = $deleteEndpoint;
        $this->target = $target;
    }

    /**
     * Deletes this site's endpoints in each mode for which the account holds a key. A mode without a key is
     * left out of the result.
     *
     * @since TBD
     *
     * @return array<string, string> "test" and "live" mapped to DELETED, NOTHING_MATCHED or FAILED
     */
    public function __invoke(AccountDetail $account): array
    {
        $results = [];

        foreach (['test' => $account->testSecretKey, 'live' => $account->liveSecretKey] as $mode => $key) {
            if (!$key) {
                continue;
            }

            $results[$mode] = $this->deleteInMode($account, $mode);
        }

        return $results;
    }

    /**
     * @since TBD
     *
     * @param string $mode "live" or "test"
     *
     * @return string DELETED, NOTHING_MATCHED or FAILED
     */
    private function deleteInMode(AccountDetail $account, string $mode): string
    {
        try {
            $endpoints = ($this->listEndpoints)($account, $mode);
            $deleted = false;

            while ($endpoint = $this->target->findIn($endpoints)) {
                $this->delete($account, $mode, $endpoint->id);
                $deleted = true;

                WebhookLogger::info('Stripe webhook endpoint deleted on disconnect.', [
                    'account' => $account->accountSlug,
                    'account_type' => $account->type,
                    'mode' => $mode,
                    'endpoint_id' => $endpoint->id,
                ]);

                $endpoints = array_values(
                    array_filter($endpoints, static function ($other) use ($endpoint) {
                        return $other->id !== $endpoint->id;
                    })
                );
            }

            return $deleted ? self::DELETED : self::NOTHING_MATCHED;
        } catch (Throwable $exception) {
            WebhookLogger::warning(
                'Stripe webhook endpoint was not deleted on disconnect.',
                array_merge(
                    ['account' => $account->accountSlug, 'account_type' => $account->type, 'mode' => $mode],
                    WebhookLogger::failureContext($exception)
                )
            );

            return self::FAILED;
        }
    }

    /**
     * Stripe answers 404 when the endpoint is already gone, which is the state this action wants.
     *
     * @since TBD
     *
     * @param string $mode "live" or "test"
     *
     * @throws WebhookEndpointException
     */
    private function delete(AccountDetail $account, string $mode, string $endpointId): void
    {
        try {
            ($this->deleteEndpoint)($account, $mode, $endpointId);
        } catch (WebhookEndpointException $exception) {
            if (404 !== $exception->getHttpStatus()) {
                throw $exception;
            }
        }
    }
}

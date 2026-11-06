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
    public const KEPT = 'kept';
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
     * @param AccountDetail   $account           The account being disconnected.
     * @param AccountDetail[] $remainingAccounts The accounts still saved. An endpoint one of them also uses is kept.
     *
     * @return array<string, string> "test" and "live" mapped to DELETED, NOTHING_MATCHED, KEPT or FAILED
     */
    public function __invoke(AccountDetail $account, array $remainingAccounts = []): array
    {
        $results = [];

        foreach (['test' => $account->testSecretKey, 'live' => $account->liveSecretKey] as $mode => $key) {
            if (!$key) {
                continue;
            }

            $results[$mode] = $this->deleteInMode($account, $mode, $remainingAccounts);
        }

        return $results;
    }

    /**
     * @since TBD
     *
     * @param string          $mode              "live" or "test"
     * @param AccountDetail[] $remainingAccounts The accounts still saved.
     *
     * @return string DELETED, NOTHING_MATCHED, KEPT or FAILED
     */
    private function deleteInMode(AccountDetail $account, string $mode, array $remainingAccounts): string
    {
        try {
            $endpoints = ($this->listEndpoints)($account, $mode);
            $deleted = false;
            $kept = false;
            $usedByOthers = false;

            while ($endpoint = $this->target->findIn($endpoints)) {
                $endpoints = array_values(
                    array_filter($endpoints, static function ($other) use ($endpoint) {
                        return $other->id !== $endpoint->id;
                    })
                );

                $usedByOthers = false === $usedByOthers ?
                    $this->endpointIdsUsedByOthers($account, $remainingAccounts, $mode) :
                    $usedByOthers;

                if (null === $usedByOthers || in_array($endpoint->id, $usedByOthers, true)) {
                    $kept = true;

                    WebhookLogger::info('Stripe webhook endpoint kept on disconnect: another saved account uses it.', [
                        'account' => $account->accountSlug,
                        'account_type' => $account->type,
                        'mode' => $mode,
                        'endpoint_id' => $endpoint->id,
                    ]);

                    continue;
                }

                $this->delete($account, $mode, $endpoint->id);
                $deleted = true;

                WebhookLogger::info('Stripe webhook endpoint deleted on disconnect.', [
                    'account' => $account->accountSlug,
                    'account_type' => $account->type,
                    'mode' => $mode,
                    'endpoint_id' => $endpoint->id,
                ]);
            }

            if ($deleted) {
                return self::DELETED;
            }

            return $kept ? self::KEPT : self::NOTHING_MATCHED;
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
     * The ids of the endpoints that the other saved accounts list in the mode, or null when every endpoint
     * must be kept. Two saved records can belong to one Stripe account, such as manual keys saved before
     * the same account was connected through GiveWP Connect, and an endpoint id is unique to its Stripe
     * account, so an id another record lists is an endpoint that record still receives events through.
     *
     * @since TBD
     *
     * @param AccountDetail   $account           The account being disconnected.
     * @param AccountDetail[] $remainingAccounts The accounts still saved.
     * @param string          $mode              "live" or "test".
     *
     * @return string[]|null Endpoint ids to keep, or null to keep every endpoint of this site.
     */
    private function endpointIdsUsedByOthers(AccountDetail $account, array $remainingAccounts, string $mode): ?array
    {
        $ids = [];

        foreach ($remainingAccounts as $other) {
            $key = 'live' === $mode ? $other->liveSecretKey : $other->testSecretKey;

            if (!$key || $other->accountSlug === $account->accountSlug) {
                continue;
            }

            if ($account->accountId && $other->accountId) {
                if ($account->accountId === $other->accountId) {
                    return null;
                }

                continue;
            }

            try {
                $ids = array_merge($ids, array_column(($this->listEndpoints)($other, $mode), 'id'));
            } catch (Throwable $exception) {
                WebhookLogger::warning(
                    'Stripe webhook endpoint kept on disconnect: another saved account could not be checked.',
                    array_merge(
                        ['account' => $other->accountSlug, 'account_type' => $other->type, 'mode' => $mode],
                        WebhookLogger::failureContext($exception)
                    )
                );

                return null;
            }
        }

        return $ids;
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

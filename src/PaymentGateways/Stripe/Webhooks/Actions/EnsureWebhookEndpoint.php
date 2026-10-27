<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\IncompleteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointLimitReached;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookSetupStatus;
use Give\PaymentGateways\Stripe\Webhooks\WebhookLogger;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;

/**
 * Makes sure a Stripe account has an enabled endpoint that sends every required event to this site.
 *
 * @since TBD
 */
final class EnsureWebhookEndpoint
{
    /**
     * Stripe requires an API version when an endpoint is created on a connected account, and the version of an
     * endpoint cannot change later. The listeners read only fields that this version still sends.
     *
     * @since TBD
     */
    public const API_VERSION = '2026-09-30.endive';

    /**
     * @var ListWebhookEndpoints
     */
    private $listEndpoints;

    /**
     * @var CreateWebhookEndpoint
     */
    private $createEndpoint;

    /**
     * @var UpdateWebhookEndpoint
     */
    private $updateEndpoint;

    /**
     * @var WebhookTarget
     */
    private $target;

    /**
     * @since TBD
     */
    public function __construct(
        ListWebhookEndpoints $listEndpoints,
        CreateWebhookEndpoint $createEndpoint,
        UpdateWebhookEndpoint $updateEndpoint,
        WebhookTarget $target
    ) {
        $this->listEndpoints = $listEndpoints;
        $this->createEndpoint = $createEndpoint;
        $this->updateEndpoint = $updateEndpoint;
        $this->target = $target;
    }

    /**
     * @since TBD
     *
     * @param string $mode "live" or "test"
     *
     * @return WebhookSetupStatus FOUND, CREATED or UPDATED
     *
     * @throws WebhookEndpointException when Stripe rejects the list or create request
     * @throws WebhookEndpointLimitReached when a new endpoint is needed and the account has no room for it
     * @throws IncompleteWebhookEndpoint when an existing endpoint could not be enabled or completed
     */
    public function __invoke(AccountDetail $account, string $mode): WebhookSetupStatus
    {
        $endpoints = ($this->listEndpoints)($account, $mode);
        $endpoint = $this->target->findIn($endpoints);

        if (!$endpoint) {
            if (count($endpoints) >= WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT) {
                throw WebhookEndpointLimitReached::create();
            }

            $created = ($this->createEndpoint)(
                $account,
                $mode,
                $this->target->url(),
                WebhookTarget::REQUIRED_EVENTS,
                self::API_VERSION,
                'GiveWP'
            );

            WebhookLogger::info('Stripe webhook endpoint created.', [
                'account' => $account->accountSlug,
                'account_type' => $account->type,
                'mode' => $mode,
                'endpoint_id' => $created->id,
                'api_version' => self::API_VERSION,
                'endpoints_on_account' => count($endpoints) + 1,
            ]);

            return WebhookSetupStatus::CREATED();
        }

        $changes = [];

        if ('enabled' !== $endpoint->status) {
            $changes['disabled'] = false;
        }

        $missingEvents = $this->target->missingEvents($endpoint);

        if ($missingEvents) {
            $changes['enabled_events'] = array_merge($endpoint->enabledEvents, $missingEvents);
        }

        if (!$changes) {
            return WebhookSetupStatus::FOUND();
        }

        try {
            ($this->updateEndpoint)($account, $mode, $endpoint->id, $changes);
        } catch (WebhookEndpointException $exception) {
            throw IncompleteWebhookEndpoint::updateFailed($endpoint->id, $exception);
        }

        WebhookLogger::info('Stripe webhook endpoint updated.', [
            'account' => $account->accountSlug,
            'account_type' => $account->type,
            'mode' => $mode,
            'endpoint_id' => $endpoint->id,
            'was_status' => $endpoint->status,
            'events_added' => $missingEvents,
        ]);

        return WebhookSetupStatus::UPDATED();
    }
}

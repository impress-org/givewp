<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks\DataTransferObjects;

use Stripe\WebhookEndpoint;

/**
 * A Stripe webhook endpoint as returned by the Stripe API.
 *
 * @since TBD
 */
final class WebhookEndpointDto
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $url;

    /**
     * Either "enabled" or "disabled".
     *
     * @var string
     */
    public $status;

    /**
     * @var string[]
     */
    public $enabledEvents;

    /**
     * Null when the endpoint uses the Stripe account's default API version.
     *
     * @var string|null
     */
    public $apiVersion;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var array<string, string>
     */
    public $metadata;

    /**
     * @var bool
     */
    public $livemode;

    /**
     * Unix timestamp.
     *
     * @var int
     */
    public $created;

    /**
     * @var string|null
     */
    public $application;

    /**
     * The signing secret. Stripe returns it only in the response to the create request.
     *
     * @var string|null
     */
    public $secret;

    /**
     * @since TBD
     */
    public static function fromStripeObject(WebhookEndpoint $endpoint): self
    {
        $self = new static();

        $self->id = $endpoint->id;
        $self->url = $endpoint->url;
        $self->status = $endpoint->status;
        $self->enabledEvents = $endpoint->enabled_events;
        $self->apiVersion = $endpoint->api_version;
        $self->description = $endpoint->description;
        $self->metadata = $endpoint->metadata ? $endpoint->metadata->toArray() : [];
        $self->livemode = $endpoint->livemode;
        $self->created = $endpoint->created;
        $self->application = $endpoint->application;
        $self->secret = $endpoint->secret ?? null;

        return $self;
    }
}

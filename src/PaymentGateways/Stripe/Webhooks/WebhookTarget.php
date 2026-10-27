<?php

/**
 * @since TBD
 */

namespace Give\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Webhooks\DataTransferObjects\WebhookEndpointDto;

/**
 * The URL that Stripe must deliver events to for this site, and the events GiveWP needs.
 *
 * @since TBD
 */
final class WebhookTarget
{
    /**
     * Stripe accepts at most this many webhook endpoints per account.
     *
     * @since TBD
     */
    public const MAX_ENDPOINTS_PER_ACCOUNT = 16;

    /**
     * The events that the Stripe listeners in GiveWP and Give Recurring handle.
     *
     * @since TBD
     */
    public const REQUIRED_EVENTS = [
        'checkout.session.completed',
        'payment_intent.succeeded',
        'payment_intent.payment_failed',
        'charge.refunded',
        'invoice.payment_succeeded',
        'invoice.payment_failed',
        'customer.subscription.created',
        'customer.subscription.deleted',
        'customer.subscription.updated',
        'customer.subscription.resumed',
    ];

    private const LISTENER_ARG = 'give-listener';
    private const LISTENER_VALUE = 'stripe';

    /**
     * The URL a new endpoint is created with.
     *
     * @since TBD
     */
    public function url(): string
    {
        return $this->listenerUrl($this->baseUrl());
    }

    /**
     * Whether a webhook endpoint URL delivers to this site's Stripe listener.
     *
     * The scheme and a trailing slash are ignored, and both the home URL and the site URL count, because the
     * settings page told admins to use the site URL before this class existed.
     *
     * @since TBD
     */
    public function matches(string $url): bool
    {
        $candidate = $this->normalize($url);

        if (!$candidate) {
            return false;
        }

        foreach (array_unique([$this->baseUrl(), site_url()]) as $baseUrl) {
            if ($candidate === $this->normalize($this->listenerUrl($baseUrl))) {
                return true;
            }
        }

        return false;
    }

    /**
     * The endpoint on the list that delivers to this site, preferring one that is enabled and complete.
     *
     * @since TBD
     *
     * @param WebhookEndpointDto[] $endpoints
     */
    public function findIn(array $endpoints): ?WebhookEndpointDto
    {
        $matching = array_values(
            array_filter($endpoints, function (WebhookEndpointDto $endpoint) {
                return $this->matches($endpoint->url);
            })
        );

        usort($matching, function (WebhookEndpointDto $a, WebhookEndpointDto $b) {
            return $this->rank($b) <=> $this->rank($a);
        });

        return $matching[0] ?? null;
    }

    /**
     * The required events that the endpoint does not send.
     *
     * @since TBD
     *
     * @return string[]
     */
    public function missingEvents(WebhookEndpointDto $endpoint): array
    {
        if (in_array('*', $endpoint->enabledEvents, true)) {
            return [];
        }

        return array_values(array_diff(self::REQUIRED_EVENTS, $endpoint->enabledEvents));
    }

    /**
     * @since TBD
     */
    private function baseUrl(): string
    {
        return defined('GIVEWP_GATEWAY_ROUTE_BASE_URL') ? GIVEWP_GATEWAY_ROUTE_BASE_URL : home_url();
    }

    /**
     * @since TBD
     */
    private function listenerUrl(string $baseUrl): string
    {
        return set_url_scheme(
            add_query_arg(self::LISTENER_ARG, self::LISTENER_VALUE, trailingslashit($baseUrl)),
            'https'
        );
    }

    /**
     * Reduces a URL to "host/path?give-listener=stripe", or an empty string when it is not a listener URL.
     *
     * @since TBD
     */
    private function normalize(string $url): string
    {
        $parts = wp_parse_url($url);

        if (empty($parts['host']) || empty($parts['query'])) {
            return '';
        }

        wp_parse_str($parts['query'], $query);

        if (self::LISTENER_VALUE !== ($query[self::LISTENER_ARG] ?? null)) {
            return '';
        }

        $host = strtolower($parts['host']) . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $path = untrailingslashit($parts['path'] ?? '');

        return $host . $path . '?' . self::LISTENER_ARG . '=' . self::LISTENER_VALUE;
    }

    /**
     * @since TBD
     */
    private function rank(WebhookEndpointDto $endpoint): int
    {
        return ('enabled' === $endpoint->status ? 2 : 0) + ($this->missingEvents($endpoint) ? 0 : 1);
    }
}

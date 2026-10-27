<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Webhooks\DataTransferObjects\WebhookEndpointDto;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use Stripe\WebhookEndpoint;

/**
 * @since TBD
 */
final class WebhookTargetTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItBuildsAnHttpsListenerUrlFromTheHomeUrl(): void
    {
        $expected = set_url_scheme(trailingslashit(home_url()) . '?give-listener=stripe', 'https');

        $this->assertSame($expected, (new WebhookTarget())->url());
    }

    /**
     * @since TBD
     *
     * @dataProvider matchingUrlProvider
     */
    public function testItMatchesListenerUrlsOfThisSite(string $url): void
    {
        $this->assertTrue((new WebhookTarget())->matches($url));
    }

    /**
     * @since TBD
     */
    public function matchingUrlProvider(): array
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);

        return [
            'https with trailing slash' => ["https://$host/?give-listener=stripe"],
            'http without trailing slash' => ["http://$host?give-listener=stripe"],
            'upper case host' => ['https://' . strtoupper($host) . '/?give-listener=stripe'],
            'extra query argument' => ["https://$host/?give-listener=stripe&source=givewp"],
        ];
    }

    /**
     * @since TBD
     *
     * @dataProvider otherUrlProvider
     */
    public function testItDoesNotMatchOtherUrls(string $url): void
    {
        $this->assertFalse((new WebhookTarget())->matches($url));
    }

    /**
     * @since TBD
     */
    public function otherUrlProvider(): array
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);

        return [
            'other site' => ['https://other.example/?give-listener=stripe'],
            'other listener' => ["https://$host/?give-listener=paypal"],
            'no listener' => ["https://$host/"],
            'other path' => ["https://$host/donate/?give-listener=stripe"],
            'not a url' => ['give-listener=stripe'],
        ];
    }

    /**
     * @since TBD
     */
    public function testItTreatsTheWildcardEventAsComplete(): void
    {
        $endpoint = $this->endpoint(['enabled_events' => ['*']]);

        $this->assertSame([], (new WebhookTarget())->missingEvents($endpoint));
    }

    /**
     * @since TBD
     */
    public function testItListsTheRequiredEventsTheEndpointDoesNotSend(): void
    {
        $sent = array_slice(WebhookTarget::REQUIRED_EVENTS, 0, 3);
        $expected = array_values(array_diff(WebhookTarget::REQUIRED_EVENTS, $sent));

        $missing = (new WebhookTarget())->missingEvents($this->endpoint(['enabled_events' => $sent]));

        $this->assertSame($expected, $missing);
    }

    /**
     * @since TBD
     */
    public function testItPrefersTheEnabledCompleteEndpointForThisSite(): void
    {
        $url = home_url('/?give-listener=stripe');
        $endpoints = [
            $this->endpoint(['id' => 'we_other', 'url' => 'https://other.example/?give-listener=stripe']),
            $this->endpoint(['id' => 'we_disabled', 'url' => $url, 'status' => 'disabled']),
            $this->endpoint(['id' => 'we_partial', 'url' => $url, 'enabled_events' => ['charge.refunded']]),
            $this->endpoint(['id' => 'we_complete', 'url' => $url, 'enabled_events' => WebhookTarget::REQUIRED_EVENTS]),
        ];

        $this->assertSame('we_complete', (new WebhookTarget())->findIn($endpoints)->id);
        $this->assertSame('we_partial', (new WebhookTarget())->findIn(array_slice($endpoints, 0, 3))->id);
        $this->assertNull((new WebhookTarget())->findIn(array_slice($endpoints, 0, 1)));
    }

    /**
     * @since TBD
     */
    public function testItKeepsTheSubdirectoryOfTheHomeUrl(): void
    {
        $root = untrailingslashit(home_url());
        update_option('home', $root . '/blog');
        update_option('siteurl', $root . '/blog');
        $host = wp_parse_url($root, PHP_URL_HOST);
        $target = new WebhookTarget();

        $this->assertSame("https://$host/blog/?give-listener=stripe", $target->url());
        $this->assertTrue($target->matches("http://$host/blog?give-listener=stripe"));
        $this->assertFalse($target->matches("https://$host/?give-listener=stripe"));
    }

    /**
     * @since TBD
     */
    public function testItMatchesBothTheHomeUrlAndTheSiteUrl(): void
    {
        $root = untrailingslashit(home_url());
        update_option('siteurl', $root . '/wp');
        $target = new WebhookTarget();

        $this->assertSame(set_url_scheme($root . '/?give-listener=stripe', 'https'), $target->url());
        $this->assertTrue($target->matches($root . '/?give-listener=stripe'));
        $this->assertTrue($target->matches($root . '/wp/?give-listener=stripe'));
        $this->assertFalse($target->matches($root . '/blog/?give-listener=stripe'));
    }

    /**
     * @since TBD
     */
    public function testItKeepsThePortOfTheHomeUrl(): void
    {
        $host = wp_parse_url(home_url(), PHP_URL_HOST);
        /* Any port other than the default one of either scheme. */
        $port = 8443;
        update_option('home', "http://$host:$port");
        update_option('siteurl', "http://$host:$port");
        $target = new WebhookTarget();

        $this->assertSame("https://$host:$port/?give-listener=stripe", $target->url());
        $this->assertTrue($target->matches("https://$host:$port/?give-listener=stripe"));
        $this->assertFalse($target->matches("https://$host/?give-listener=stripe"));
    }

    /**
     * @since TBD
     */
    private function endpoint(array $overrides): WebhookEndpointDto
    {
        return WebhookEndpointDto::fromStripeObject(WebhookEndpoint::constructFrom($this->endpointBody($overrides)));
    }
}

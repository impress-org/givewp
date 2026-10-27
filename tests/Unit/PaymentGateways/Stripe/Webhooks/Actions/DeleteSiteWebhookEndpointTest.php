<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\Log\ValueObjects\LogType;
use Give\PaymentGateways\Stripe\Webhooks\Actions\DeleteSiteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\ReadsGiveLog;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;
use RuntimeException;

/**
 * @since TBD
 */
final class DeleteSiteWebhookEndpointTest extends TestCase
{
    use ReadsGiveLog;
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItDeletesOnlyTheEndpointForThisSiteInEachMode(): void
    {
        $account = $this->makeAccount();
        $other = $this->endpointBody(['id' => 'we_other', 'url' => 'https://other.example.org/?give-listener=stripe']);

        foreach (['test', 'live'] as $mode) {
            $this->stripe->queue(200, $this->listBody([$other, $this->siteEndpoint('we_site' . $mode)]));
            $this->stripe->queue(200, ['id' => 'we_site' . $mode, 'object' => 'webhook_endpoint', 'deleted' => true]);
        }

        $results = (give(DeleteSiteWebhookEndpoint::class))($account);

        $this->assertSame(
            ['test' => DeleteSiteWebhookEndpoint::DELETED, 'live' => DeleteSiteWebhookEndpoint::DELETED],
            $results
        );
        $this->assertSame(['get', 'delete', 'get', 'delete'], array_column($this->stripe->requests, 'method'));
        $this->assertStringEndsWith('/v1/webhook_endpoints/we_sitetest', $this->stripe->requests[1]['absUrl']);
        $this->assertStringEndsWith('/v1/webhook_endpoints/we_sitelive', $this->stripe->requests[3]['absUrl']);
        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(1, 'Authorization'));
        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(2, 'Authorization'));
        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(3, 'Authorization'));
    }

    /**
     * @since TBD
     */
    public function testItSkipsAModeWithoutAKey(): void
    {
        $account = $this->makeAccount(['live_secret_key' => '', 'live_publishable_key' => '']);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));
        $this->stripe->queue(200, ['id' => 'we_123', 'object' => 'webhook_endpoint', 'deleted' => true]);

        $results = (give(DeleteSiteWebhookEndpoint::class))($account);

        $this->assertSame(['test' => DeleteSiteWebhookEndpoint::DELETED], $results);
        $this->assertCount(2, $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function testItDoesNotDeleteWhenNoEndpointMatchesThisSite(): void
    {
        $account = $this->makeAccount(['live_secret_key' => '']);
        $other = $this->endpointBody(['url' => 'https://other.example.org/?give-listener=stripe']);
        $unrelated = $this->endpointBody(['id' => 'we_unrelated', 'url' => home_url('/some-other-endpoint')]);
        $this->stripe->queue(200, $this->listBody([$other, $unrelated]));

        $results = (give(DeleteSiteWebhookEndpoint::class))($account);

        $this->assertSame(['test' => DeleteSiteWebhookEndpoint::NOTHING_MATCHED], $results);
        $this->assertSame(['get'], array_column($this->stripe->requests, 'method'));
    }

    /**
     * @since TBD
     */
    public function testItTreatsAnEndpointThatIsAlreadyGoneAsDeleted(): void
    {
        $account = $this->makeAccount(['live_secret_key' => '']);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));
        $this->stripe->queue(404, $this->errorBody('invalid_request_error', 'No such webhook endpoint: we_123'));

        $results = (give(DeleteSiteWebhookEndpoint::class))($account);

        $this->assertSame(['test' => DeleteSiteWebhookEndpoint::DELETED], $results);
        $this->assertSame([], $this->webhookLogs(LogType::WARNING, $account->accountSlug));
    }

    /**
     * @since TBD
     */
    public function testItLogsAStripeErrorAndStillTriesTheOtherMode(): void
    {
        $account = $this->makeAccount(['account_slug' => 'acct_revoked']);
        $error = $this->errorBody('authentication_error', 'Invalid API Key provided.');
        $this->stripe->queue(401, $error);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));
        $this->stripe->queue(200, ['id' => 'we_123', 'object' => 'webhook_endpoint', 'deleted' => true]);

        $results = (give(DeleteSiteWebhookEndpoint::class))($account);

        $this->assertSame(
            ['test' => DeleteSiteWebhookEndpoint::FAILED, 'live' => DeleteSiteWebhookEndpoint::DELETED],
            $results
        );

        $failed = $this->webhookLogs(LogType::WARNING, $account->accountSlug);
        $this->assertCount(1, $failed);

        $context = $failed[0]->getContext();
        $this->assertSame('test', $context['mode']);
        $this->assertSame(401, $context['stripe_http_status']);
        $this->assertSame('authentication_error', $context['stripe_error_type']);
        $this->assertSame($error['error']['message'], $context['error']);
        $this->assertStringNotContainsString($account->testSecretKey, wp_json_encode($context));
        $this->assertStringNotContainsString($account->liveSecretKey, wp_json_encode($context));
    }

    /**
     * @since TBD
     */
    public function testItLogsAnUnexpectedErrorInsteadOfThrowing(): void
    {
        $account = $this->makeAccount(['live_secret_key' => '']);
        $this->stripe->queueException(new RuntimeException('Connection reset'));

        $results = (give(DeleteSiteWebhookEndpoint::class))($account);

        $this->assertSame(['test' => DeleteSiteWebhookEndpoint::FAILED], $results);
        $this->assertCount(1, $this->webhookLogs(LogType::WARNING, $account->accountSlug));
    }

    /**
     * @since TBD
     */
    private function siteEndpoint(string $id = 'we_123'): array
    {
        return $this->endpointBody([
            'id' => $id,
            'url' => (new WebhookTarget())->url(),
            'enabled_events' => WebhookTarget::REQUIRED_EVENTS,
        ]);
    }
}

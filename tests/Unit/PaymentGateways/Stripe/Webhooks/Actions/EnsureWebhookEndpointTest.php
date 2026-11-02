<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Webhooks\Actions\EnsureWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\IncompleteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointLimitReached;
use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookSetupStatus;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;

/**
 * @since TBD
 */
final class EnsureWebhookEndpointTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItLeavesAnEnabledCompleteEndpointAlone(): void
    {
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint()]));

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertTrue($status->isFound());
        $this->assertCount(1, $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function testItCreatesTheEndpointWhenNoneMatchesThisSite(): void
    {
        $target = new WebhookTarget();
        $this->stripe->queue(200, $this->listBody([$this->endpointBody(['url' => 'https://other.example/?give-listener=stripe'])]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_new', 'url' => $target->url(), 'secret' => 'whsec_new']));

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertTrue($status->isCreated());

        $create = $this->stripe->requests[1];
        $this->assertSame('post', $create['method']);
        $this->assertStringEndsWith('/v1/webhook_endpoints', $create['absUrl']);
        $this->assertSame($target->url(), $create['params']['url']);
        $this->assertSame(WebhookTarget::REQUIRED_EVENTS, $create['params']['enabled_events']);
        $this->assertSame(EnsureWebhookEndpoint::API_VERSION, $create['params']['api_version']);
    }

    /**
     * @since TBD
     */
    public function testItEnablesTheEndpointAndAddsTheMissingEvents(): void
    {
        $sent = ['charge.refunded', 'account.updated'];
        $endpoint = $this->siteEndpoint(['status' => 'disabled', 'enabled_events' => $sent]);
        $this->stripe->queue(200, $this->listBody([$endpoint]));
        $this->stripe->queue(200, $this->siteEndpoint());

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertTrue($status->isUpdated());

        $update = $this->stripe->requests[1];
        $this->assertStringEndsWith('/v1/webhook_endpoints/' . $endpoint['id'], $update['absUrl']);
        $this->assertSame('false', $update['params']['disabled']);
        $this->assertSame(
            array_merge($sent, array_values(array_diff(WebhookTarget::REQUIRED_EVENTS, $sent))),
            $update['params']['enabled_events']
        );
    }

    /**
     * @since TBD
     */
    public function testItDoesNotCreateAnEndpointWhenTheAccountIsFull(): void
    {
        $endpoints = [];

        for ($index = 0; $index < WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT; $index++) {
            $endpoints[] = $this->endpointBody(['id' => "we_$index", 'url' => "https://other$index.example/"]);
        }

        $this->stripe->queue(200, $this->listBody($endpoints));
        $this->expectException(WebhookEndpointLimitReached::class);

        try {
            give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');
        } finally {
            $this->assertCount(1, $this->stripe->requests);
        }
    }

    /**
     * @since TBD
     */
    public function testItReportsAnIncompleteEndpointWhenTheUpdateFails(): void
    {
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint(['status' => 'disabled'])]));
        $this->stripe->queue(403, $this->errorBody('invalid_request_error', 'The key does not have permission.'));

        try {
            give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');
            $this->fail('Expected IncompleteWebhookEndpoint.');
        } catch (IncompleteWebhookEndpoint $exception) {
            $this->assertInstanceOf(WebhookEndpointException::class, $exception->getPrevious());
            $this->assertTrue(WebhookSetupStatus::fromException($exception)->isIncomplete());
        }
    }

    /**
     * @since TBD
     */
    public function testItClassifiesARejectedTokenAsAnAuthorizationProblem(): void
    {
        $this->stripe->queue(401, $this->errorBody('invalid_request_error', 'Invalid API Key provided.'));

        try {
            give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');
            $this->fail('Expected WebhookEndpointException.');
        } catch (WebhookEndpointException $exception) {
            $this->assertTrue(WebhookSetupStatus::fromException($exception)->isAuthorization());
        }
    }

    /**
     * @since TBD
     */
    public function testItLeavesAnEndpointThatSendsEveryEventAlone(): void
    {
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint(['enabled_events' => ['*']])]));

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertTrue($status->isFound());
        $this->assertCount(1, $this->stripe->requests);
    }

    /**
     * @since TBD
     */
    public function testItSendsOnlyTheEventsToAnEnabledEndpointThatMissesSome(): void
    {
        $sent = array_slice(WebhookTarget::REQUIRED_EVENTS, 0, 2);
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint(['enabled_events' => $sent])]));
        $this->stripe->queue(200, $this->siteEndpoint());

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertTrue($status->isUpdated());
        $this->assertSame(['enabled_events'], array_keys($this->stripe->requests[1]['params']));
        $this->assertSame(WebhookTarget::REQUIRED_EVENTS, $this->stripe->requests[1]['params']['enabled_events']);
    }

    /**
     * @since TBD
     */
    public function testItOnlyEnablesADisabledEndpointThatSendsEveryEvent(): void
    {
        $this->stripe->queue(200, $this->listBody([$this->siteEndpoint(['status' => 'disabled'])]));
        $this->stripe->queue(200, $this->siteEndpoint());

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertTrue($status->isUpdated());
        /* The SDK encodes booleans as strings in the form body. */
        $this->assertSame(['disabled' => 'false'], $this->stripe->requests[1]['params']);
    }

    /**
     * @since TBD
     */
    public function testItCreatesTheEndpointWhenTheAccountHasRoomForOneMore(): void
    {
        $endpoints = $this->otherSiteEndpoints(WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT - 1);
        $this->stripe->queue(200, $this->listBody($endpoints));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_new', 'secret' => 'whsec_new']));

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertTrue($status->isCreated());
        $this->assertSame('post', $this->stripe->requests[1]['method']);
        $this->assertStringEndsWith('/v1/webhook_endpoints', $this->stripe->requests[1]['absUrl']);
    }

    /**
     * @since TBD
     */
    public function testItUpdatesTheSiteEndpointOnAFullAccount(): void
    {
        $endpoints = $this->otherSiteEndpoints(WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT - 1);
        $endpoints[] = $this->siteEndpoint(['id' => 'we_site', 'status' => 'disabled']);
        $this->stripe->queue(200, $this->listBody($endpoints));
        $this->stripe->queue(200, $this->siteEndpoint(['id' => 'we_site']));

        $status = give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        $this->assertCount(WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT, $endpoints);
        $this->assertTrue($status->isUpdated());
        $this->assertStringEndsWith('/v1/webhook_endpoints/we_site', $this->stripe->requests[1]['absUrl']);
    }

    /**
     * @since TBD
     */
    public function testItReportsAFailedCreateAsAStripeError(): void
    {
        $error = $this->errorBody('invalid_request_error', 'Invalid URL: must be HTTPS in live mode.');
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(400, $error);

        try {
            give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');
            $this->fail('Expected WebhookEndpointException.');
        } catch (WebhookEndpointException $exception) {
            $this->assertSame($error['error']['message'], $exception->getMessage());
            $this->assertTrue(WebhookSetupStatus::fromException($exception)->isInvalidRequest());
        }
    }

    /**
     * @since TBD
     */
    public function testItAuthenticatesWithTheLiveKeyInLiveMode(): void
    {
        $account = $this->makeAccount();
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_new', 'livemode' => true]));

        give(EnsureWebhookEndpoint::class)($account, 'live');

        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(0, 'Authorization'));
        $this->assertSame('Bearer ' . $account->liveSecretKey, $this->stripe->header(1, 'Authorization'));
    }

    /**
     * @since TBD
     */
    public function testItNamesTheCreatedEndpointAfterGiveWP(): void
    {
        $this->stripe->queue(200, $this->listBody([]));
        $this->stripe->queue(200, $this->endpointBody(['id' => 'we_new']));

        give(EnsureWebhookEndpoint::class)($this->makeAccount(), 'test');

        /* The description lets admins tell the GiveWP endpoint apart in the Stripe Dashboard. */
        $this->assertSame('GiveWP', $this->stripe->requests[1]['params']['description']);
    }

    /**
     * @since TBD
     */
    private function siteEndpoint(array $overrides = []): array
    {
        return $this->endpointBody(
            array_merge(
                ['url' => home_url('/?give-listener=stripe'), 'enabled_events' => WebhookTarget::REQUIRED_EVENTS],
                $overrides
            )
        );
    }

    /**
     * @since TBD
     *
     * @return array[] Endpoint bodies whose URLs point at other sites.
     */
    private function otherSiteEndpoints(int $count): array
    {
        $endpoints = [];

        for ($index = 0; $index < $count; $index++) {
            $endpoints[] = $this->endpointBody([
                'id' => "we_other$index",
                'url' => "https://other$index.example/?give-listener=stripe",
            ]);
        }

        return $endpoints;
    }
}

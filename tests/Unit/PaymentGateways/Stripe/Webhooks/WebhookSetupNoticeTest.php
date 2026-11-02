<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks;

use Give\PaymentGateways\Stripe\Webhooks\ValueObjects\WebhookSetupStatus;
use Give\PaymentGateways\Stripe\Webhooks\WebhookSetupNotice;
use Give\PaymentGateways\Stripe\Webhooks\WebhookTarget;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;

/**
 * @since TBD
 */
final class WebhookSetupNoticeTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItReportsSuccessWhenEveryAccountIsSetUp(): void
    {
        $notice = give(WebhookSetupNotice::class)->build(
            [
                ['account' => $this->makeAccount(), 'status' => WebhookSetupStatus::CREATED(), 'message' => ''],
                ['account' => $this->makeAccount(), 'status' => WebhookSetupStatus::FOUND(), 'message' => ''],
            ],
            'test'
        );

        $this->assertSame('success', $notice['level']);
        $this->assertFalse($notice['retry']);
    }

    /**
     * @since TBD
     */
    public function testItTellsAConnectAccountToReconnectAndAManualAccountToFixItsKey(): void
    {
        $connect = $this->makeAccount(['account_name' => 'Connect']);
        $manual = $this->makeAccount(['type' => 'manual', 'account_name' => 'Manual']);

        $notice = give(WebhookSetupNotice::class)->build(
            [
                ['account' => $connect, 'status' => WebhookSetupStatus::AUTHORIZATION(), 'message' => ''],
                ['account' => $manual, 'status' => WebhookSetupStatus::AUTHORIZATION(), 'message' => ''],
            ],
            'test'
        );

        $this->assertSame('warning', $notice['level']);
        $this->assertTrue($notice['retry']);
        $this->assertStringContainsString('connect it again', $notice['body']);
        $this->assertStringContainsString('Webhook Endpoints', $notice['body']);
    }

    /**
     * @since TBD
     */
    public function testItFallsBackToTheManualStepsOnAnUnknownError(): void
    {
        $notice = give(WebhookSetupNotice::class)->build(
            [['account' => $this->makeAccount(), 'status' => WebhookSetupStatus::UNKNOWN(), 'message' => 'Boom']],
            'test'
        );

        $this->assertSame('error', $notice['level']);
        $this->assertStringContainsString('dashboard.stripe.com/webhooks', $notice['body']);
        $this->assertStringContainsString('group=webhooks', $notice['body']);
    }

    /**
     * @since TBD
     */
    public function testItListsTheRequiredEventsForAnIncompleteEndpoint(): void
    {
        $body = $this->buildFor(WebhookSetupStatus::INCOMPLETE())['body'];

        $this->assertStringContainsString(esc_html(implode(', ', WebhookTarget::REQUIRED_EVENTS)), $body);
        $this->assertStringContainsString('dashboard.stripe.com/webhooks', $body);
    }

    /**
     * @since TBD
     */
    public function testItStatesTheEndpointLimitForAFullAccount(): void
    {
        $body = $this->buildFor(WebhookSetupStatus::LIMIT_REACHED())['body'];

        $this->assertStringContainsString(
            'already has ' . WebhookTarget::MAX_ENDPOINTS_PER_ACCOUNT . ' webhook endpoints',
            $body
        );
    }

    /**
     * @since TBD
     */
    public function testItAsksToWaitWhenStripeRateLimitedTheRequest(): void
    {
        $notice = $this->buildFor(WebhookSetupStatus::RATE_LIMITED());

        $this->assertSame('warning', $notice['level']);
        $this->assertTrue($notice['retry']);
        $this->assertStringContainsString('too many requests', $notice['body']);
    }

    /**
     * @since TBD
     */
    public function testItPointsAtTheServerWhenStripeCannotBeReached(): void
    {
        $notice = $this->buildFor(WebhookSetupStatus::CONNECTION());

        $this->assertSame('warning', $notice['level']);
        $this->assertTrue($notice['retry']);
        $this->assertStringContainsString('outgoing HTTPS requests', $notice['body']);
    }

    /**
     * @since TBD
     */
    public function testItNamesTheModeTheEndpointWasSetUpFor(): void
    {
        $results = [['account' => $this->makeAccount(), 'status' => WebhookSetupStatus::FOUND(), 'message' => '']];

        $live = give(WebhookSetupNotice::class)->build($results, 'live');
        $test = give(WebhookSetupNotice::class)->build($results, 'test');

        $this->assertStringContainsString('live mode', $live['body']);
        $this->assertStringNotContainsString('test mode', $live['body']);
        $this->assertStringContainsString('test mode', $test['body']);
        $this->assertStringContainsString(esc_html((new WebhookTarget())->url()), $live['body']);
    }

    /**
     * @since TBD
     */
    public function testItShowsTheManualStepsWhenThereIsNoAccount(): void
    {
        $notice = give(WebhookSetupNotice::class)->build([], 'test');

        $this->assertSame('error', $notice['level']);
        $this->assertTrue($notice['retry']);
        $this->assertStringContainsString('dashboard.stripe.com/webhooks', $notice['body']);
    }

    /**
     * @since TBD
     */
    public function testItEscapesTheStripeErrorMessage(): void
    {
        $notice = give(WebhookSetupNotice::class)->build(
            [
                [
                    'account' => $this->makeAccount(),
                    'status' => WebhookSetupStatus::INVALID_REQUEST(),
                    'message' => '<b>Invalid URL</b>',
                ],
            ],
            'test'
        );

        $this->assertStringContainsString('&lt;b&gt;Invalid URL&lt;/b&gt;', $notice['body']);
    }

    /**
     * @since TBD
     *
     * @return array{level: string, title: string, body: string, retry: bool}
     */
    private function buildFor(WebhookSetupStatus $status): array
    {
        return give(WebhookSetupNotice::class)->build(
            [['account' => $this->makeAccount(), 'status' => $status, 'message' => '']],
            'test'
        );
    }
}

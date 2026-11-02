<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Actions;

use Give\PaymentGateways\Stripe\Webhooks\Actions\DeleteWebhookEndpoint;
use Give\PaymentGateways\Stripe\Webhooks\Exceptions\WebhookEndpointException;
use Give\Tests\TestCase;
use Give\Tests\Unit\PaymentGateways\Stripe\Webhooks\Support\UsesFakeStripeApi;

/**
 * @since TBD
 */
final class DeleteWebhookEndpointTest extends TestCase
{
    use UsesFakeStripeApi;

    /**
     * @since TBD
     */
    public function testItDeletesTheEndpointAsTheConnectedAccount(): void
    {
        $account = $this->makeAccount();
        $this->stripe->queue(200, ['id' => 'we_123', 'object' => 'webhook_endpoint', 'deleted' => true]);

        (new DeleteWebhookEndpoint())($account, 'test', 'we_123');

        $request = $this->stripe->requests[0];
        $this->assertSame('delete', $request['method']);
        $this->assertStringEndsWith('/v1/webhook_endpoints/we_123', $request['absUrl']);
        $this->assertSame('Bearer ' . $account->testSecretKey, $this->stripe->header(0, 'Authorization'));
    }

    /**
     * @since TBD
     */
    public function testItExposesTheNotFoundStatusSoACallerCanTreatItAsAlreadyDeleted(): void
    {
        $this->stripe->queue(404, $this->errorBody('invalid_request_error', 'No such webhook endpoint: we_gone'));

        try {
            (new DeleteWebhookEndpoint())($this->makeAccount(), 'test', 'we_gone');
            $this->fail('Expected a WebhookEndpointException.');
        } catch (WebhookEndpointException $exception) {
            $this->assertSame(404, $exception->getHttpStatus());
        }
    }

    /**
     * @since TBD
     */
    public function testItEscapesTheRejectedIdInTheExceptionMessage(): void
    {
        $endpointId = '<b>we_1</b>';

        try {
            (new DeleteWebhookEndpoint())($this->makeAccount(), 'test', $endpointId);
            $this->fail('Expected a WebhookEndpointException.');
        } catch (WebhookEndpointException $exception) {
            $this->assertStringNotContainsString($endpointId, $exception->getMessage());
            $this->assertStringContainsString(esc_html($endpointId), $exception->getMessage());
        }
    }

    /**
     * @since TBD
     */
    public function testItRefusesAnIdThatIsNotAWebhookEndpointId(): void
    {
        $this->expectException(WebhookEndpointException::class);

        try {
            (new DeleteWebhookEndpoint())($this->makeAccount(), 'test', 'cus_123');
        } finally {
            $this->assertSame([], $this->stripe->requests);
        }
    }
}

<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalStandard\Webhooks;

use Give\PaymentGateways\Gateways\PayPalStandard\Webhooks\WebhookValidator;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.18.0.1
 */
class WebhookValidatorTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array|null Arguments of the last request sent to PayPal.
     */
    private $requestArgs;

    /**
     * @var string Body PayPal answers the IPN postback with.
     */
    private $responseBody = 'VERIFIED';

    public function setUp(): void
    {
        parent::setUp();

        add_filter('pre_http_request', [$this, 'mockPayPalResponse'], 10, 2);
    }

    public function tearDown(): void
    {
        remove_filter('pre_http_request', [$this, 'mockPayPalResponse']);

        parent::tearDown();
    }

    /**
     * @since 4.18.0.1
     */
    public function testPostbackVerifiesSslCertificate(): void
    {
        $this->assertTrue((new WebhookValidator())->verifyEventSignature(['txn_id' => 'TXN']));
        $this->assertTrue($this->requestArgs['sslverify']);
    }

    /**
     * The setting was removed in 2.15.0, but a stale stored value must not skip the postback.
     *
     * @since 4.18.0.1
     */
    public function testPostbackRunsWhenLegacyVerificationSettingIsDisabled(): void
    {
        give_update_option('paypal_verification', 'disabled');
        $this->responseBody = 'INVALID';

        $this->assertFalse((new WebhookValidator())->verifyEventSignature(['txn_id' => 'TXN']));
        $this->assertNotNull($this->requestArgs);
    }

    /**
     * @since 4.18.0.1
     *
     * @param false|array $preempt Response to short-circuit the request with.
     * @param array       $args    Arguments of the request sent to PayPal.
     *
     * @return array Mocked PayPal response.
     */
    public function mockPayPalResponse($preempt, array $args): array
    {
        $this->requestArgs = $args;

        return [
            'headers'  => [],
            'body'     => $this->responseBody,
            'response' => ['code' => 200, 'message' => 'OK'],
            'cookies'  => [],
            'filename' => null,
        ];
    }
}

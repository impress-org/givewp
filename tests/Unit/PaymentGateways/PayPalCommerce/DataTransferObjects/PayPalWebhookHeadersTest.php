<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce\DataTransferObjects;

use Give\Framework\Exceptions\Primitives\HttpHeaderException;
use Give\PaymentGateways\PayPalCommerce\DataTransferObjects\PayPalWebhookHeaders;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since 4.3.2
 */
class PayPalWebhookHeadersTest extends TestCase
{
    /**
     * @since 4.3.2
     *
     * @return array
     */
    private function getDefaultHeaders(): array
    {
        return [
            'paypal-transmission-id' => 'test-id',
            'paypal-transmission-time' => '2023-01-01T00:00:00Z',
            'paypal-transmission-sig' => 'test-sig',
            'paypal-cert-url' => 'https://test.com/cert',
            'paypal-auth-algo' => 'SHA256',
        ];
    }

    /**
     * @since 4.3.2
     *
     * @return void
     */
    public function testFromHeadersWithStandardFormat()
    {
        $webhookHeaders = PayPalWebhookHeaders::fromHeaders($this->getDefaultHeaders());

        $this->assertEquals('test-id', $webhookHeaders->transmissionId);
        $this->assertEquals('2023-01-01T00:00:00Z', $webhookHeaders->transmissionTime);
        $this->assertEquals('test-sig', $webhookHeaders->transmissionSig);
        $this->assertEquals('https://test.com/cert', $webhookHeaders->certUrl);
        $this->assertEquals('SHA256', $webhookHeaders->authAlgo);
    }

    /**
     * @since 4.3.2
     *
     * @return void
     */
    public function testFromHeadersWithUppercaseFormat()
    {
        $headers = array_change_key_case($this->getDefaultHeaders(), CASE_UPPER);
        $webhookHeaders = PayPalWebhookHeaders::fromHeaders($headers);

        $this->assertEquals('test-id', $webhookHeaders->transmissionId);
        $this->assertEquals('2023-01-01T00:00:00Z', $webhookHeaders->transmissionTime);
        $this->assertEquals('test-sig', $webhookHeaders->transmissionSig);
        $this->assertEquals('https://test.com/cert', $webhookHeaders->certUrl);
        $this->assertEquals('SHA256', $webhookHeaders->authAlgo);
    }

    /**
     * @since 4.3.2
     *
     * @return void
     */
    public function testFromHeadersWithUnderscoreFormat()
    {
        $headers = array_combine(
            array_map(
                fn($key) => str_replace('-', '_', $key),
                array_keys($this->getDefaultHeaders())
            ),
            array_values($this->getDefaultHeaders())
        );

        $webhookHeaders = PayPalWebhookHeaders::fromHeaders($headers);

        $this->assertEquals('test-id', $webhookHeaders->transmissionId);
        $this->assertEquals('2023-01-01T00:00:00Z', $webhookHeaders->transmissionTime);
        $this->assertEquals('test-sig', $webhookHeaders->transmissionSig);
        $this->assertEquals('https://test.com/cert', $webhookHeaders->certUrl);
        $this->assertEquals('SHA256', $webhookHeaders->authAlgo);
    }

    /**
     * @since 4.3.2
     *
     * @return void
     */
    public function testFromHeadersWithMixedFormat()
    {
        $headers = [
            'Paypal-Transmission-Id' => 'test-id',
            'PAYPAL_TRANSMISSION_TIME' => '2023-01-01T00:00:00Z',
            'paypal-transmission-sig' => 'test-sig',
            'Paypal_Cert_Url' => 'https://test.com/cert',
            'PAYPAL-AUTH-ALGO' => 'SHA256',
        ];

        $webhookHeaders = PayPalWebhookHeaders::fromHeaders($headers);

        $this->assertEquals('test-id', $webhookHeaders->transmissionId);
        $this->assertEquals('2023-01-01T00:00:00Z', $webhookHeaders->transmissionTime);
        $this->assertEquals('test-sig', $webhookHeaders->transmissionSig);
        $this->assertEquals('https://test.com/cert', $webhookHeaders->certUrl);
        $this->assertEquals('SHA256', $webhookHeaders->authAlgo);
    }

    /**
     * @since 4.3.2
     *
     * @return void
     */
    public function testFromHeadersThrowsExceptionWhenMissingRequiredHeaders()
    {
        $headers = array_slice($this->getDefaultHeaders(), 0, 2);

        $this->expectException(HttpHeaderException::class);
        $this->expectExceptionMessage('Missing PayPal headers: paypal-transmission-sig, paypal-cert-url, paypal-auth-algo');

        PayPalWebhookHeaders::fromHeaders($headers);
    }

    /**
     * @since 4.3.2
     *
     * @return void
     */
    public function testFromHeadersWithEmptyHeaders()
    {
        $this->expectException(HttpHeaderException::class);
        $this->expectExceptionMessage('Missing PayPal headers: paypal-transmission-id, paypal-transmission-time, paypal-transmission-sig, paypal-cert-url, paypal-auth-algo');

        PayPalWebhookHeaders::fromHeaders([]);
    }
}

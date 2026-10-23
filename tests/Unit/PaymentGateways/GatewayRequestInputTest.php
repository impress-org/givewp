<?php

namespace Give\Tests\Unit\PaymentGateways;

use Give\Donations\Models\Donation;
use Give\Framework\PaymentGateways\Actions\HandleGatewayPaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentRefunded;
use Give\Framework\PaymentGateways\Traits\HandleHttpResponses;
use Give\PaymentGateways\Actions\GetGatewayDataFromRequest;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Checks that the gateway request readers still behave as before for valid requests.
 *
 * @since TBD
 */
final class GatewayRequestInputTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The $_SERVER values from before the test, so changes made here do not reach later tests.
     *
     * @since TBD
     */
    private array $serverBackup = [];

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        $this->serverBackup = $_SERVER;

        parent::setUp();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_REQUEST = [];

        parent::tearDown();

        $_SERVER = $this->serverBackup;
    }

    /**
     * @since TBD
     */
    public function testGatewayDataKeepsBackslashesAndQuotesFromTheRequest(): void
    {
        $_REQUEST['gatewayData'] = wp_slash(['note' => 'a\\b "double" \'single\'']);

        $this->assertSame(['note' => 'a\\b "double" \'single\''], (new GetGatewayDataFromRequest())());
    }

    /**
     * @since TBD
     */
    public function testRequestIsJsonReadsTheContentType(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json; charset=utf-8';

        $this->assertTrue($this->callRequestIsJson());

        $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';

        $this->assertFalse($this->callRequestIsJson());
    }

    /**
     * @since TBD
     */
    public function testWantsJsonReadsTheAcceptAndContentTypeHeaders(): void
    {
        $handler = new class {
            use HandleHttpResponses;

            public function check(): bool
            {
                return $this->wantsJson();
            }
        };

        $this->assertFalse($handler->check());

        $_SERVER['HTTP_ACCEPT'] = 'text/html, application/json';
        $this->assertTrue($handler->check());

        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        $this->assertTrue($handler->check());
    }

    /**
     * @since TBD
     */
    public function testRefundRedirectsBackToTheSlashedReferer(): void
    {
        $donation = Donation::factory()->create();
        $_REQUEST['_wp_http_referer'] = wp_slash('/wp-admin/edit.php?post_type=give_forms&page=give-payment-history');

        $response = (new HandleGatewayPaymentCommand())(new PaymentRefunded(), $donation);

        $this->assertSame(
            home_url('/wp-admin/edit.php?post_type=give_forms&page=give-payment-history'),
            $response->getTargetUrl()
        );
    }

    /**
     * @since TBD
     */
    public function testRefundRedirectsHomeWithoutAReferer(): void
    {
        $donation = Donation::factory()->create();

        $response = (new HandleGatewayPaymentCommand())(new PaymentRefunded(), $donation);

        $this->assertSame(home_url('/'), $response->getTargetUrl());
    }

    private function callRequestIsJson(): bool
    {
        $reader = new class extends GetGatewayDataFromRequest {
            public function check(): bool
            {
                return $this->requestIsJson();
            }
        };

        return $reader->check();
    }
}

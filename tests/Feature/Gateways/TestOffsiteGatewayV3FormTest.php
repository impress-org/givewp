<?php

namespace Give\Tests\Feature\Gateways;

use Give\DonationForms\Listeners\AddRedirectUrlsToGatewayData;
use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\PaymentGateways\Gateways\TestOffsiteGateway\TestOffsiteGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\InterruptsRedirects;
use Give\Tests\TestTraits\RefreshDatabase;
use WPDieException;

/**
 * A v3 form donation paid through an offsite gateway, from the point the gateway takes over.
 *
 * A v3 form hands the gateway its success URL rawurlencoded (AddRedirectUrlsToGatewayData), and that URL
 * carries the confirmation receipt's own query string. The gateway signs it into the offsite return URL,
 * and coming back lands on GatewayRoute, which rebuilds the signature and completes the donation. The
 * ampersand in that success URL is what the signature has to survive, and it is the v3 counterpart of the
 * raw URL TestOffsiteGatewayLegacyFormTest drives through the legacy processor.
 *
 * This drives the gateway and the return route directly rather than the whole donate route: the return is
 * the same GatewayRoute for every form version, and the piece that differs by version is only the shape of
 * the success URL, which this builds the way the v3 listener does.
 *
 * @since 4.16.8
 */
class TestOffsiteGatewayV3FormTest extends TestCase
{
    use RefreshDatabase;
    use InterruptsRedirects;

    /**
     * @since 4.16.8
     */
    public function setUp(): void
    {
        parent::setUp();

        // The test case turns wp_die() into WPDieException, but only for the default handler. Once an earlier
        // test has defined DOING_AJAX, wp_die() picks the ajax handler instead, which prints and exits.
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler']);
        add_filter('wp_die_json_handler', [$this, 'get_wp_die_handler']);

        /** @var PaymentGatewayRegister $registrar */
        $registrar = give(PaymentGatewayRegister::class);

        if ( ! $registrar->hasPaymentGateway(TestOffsiteGateway::id())) {
            $registrar->registerGateway(TestOffsiteGateway::class);
        }
    }

    /**
     * @since 4.16.8
     */
    public function tearDown(): void
    {
        $_GET = [];

        parent::tearDown();
    }

    /**
     * @since 4.16.8
     */
    public function testAV3FormDonationCompletesWhenTheDonorReturnsFromTheGateway()
    {
        $donation = Donation::factory()->create([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => TestOffsiteGateway::id(),
        ]);

        parse_str(parse_url($this->offsiteUrlFor($donation), PHP_URL_QUERY), $_GET);

        $receiptUrl = $this->captureRedirect(static function () {
            (new GatewayRoute())();
        });

        parse_str(parse_url($receiptUrl, PHP_URL_QUERY), $receiptArgs);

        $this->assertSame('donation-completed', $receiptArgs['givewp-event']);
        $this->assertSame($donation->purchaseKey, $receiptArgs['givewp-receipt-id']);
        $this->assertTrue(Donation::find($donation->id)->status->isComplete());
    }

    /**
     * The same signed return URL, repointed at somebody else's pending donation, is refused before the
     * route method can complete it.
     *
     * @since 4.16.8
     */
    public function testAReturnRepointedAtAnotherDonationIsRefused()
    {
        $mine = Donation::factory()->create([
            'status' => DonationStatus::PENDING(),
            'gatewayId' => TestOffsiteGateway::id(),
        ]);
        $theirs = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        parse_str(parse_url($this->offsiteUrlFor($mine), PHP_URL_QUERY), $_GET);
        $_GET['givewp-donation-id'] = $theirs->id;

        try {
            (new GatewayRoute())();
            $this->fail('The route accepted a return whose donation id had been edited.');
        } catch (WPDieException $exception) {
            $this->assertStringContainsString('Forbidden', $exception->getMessage());
        }

        $this->assertTrue(Donation::find($theirs->id)->status->isPending());
    }

    /**
     * Runs the gateway's real createPayment() with the gateway data a v3 form hands it, and returns the URL
     * it redirects the donor to.
     *
     * AddRedirectUrlsToGatewayData is what a v3 form runs to build successUrl and cancelUrl, so calling it
     * here gives the rawurlencoded, receipt-bound URL the gateway actually receives rather than a
     * hand-made stand-in.
     */
    private function offsiteUrlFor(Donation $donation): string
    {
        // The v3 listener registers a filter that adds successUrl and cancelUrl to the gateway data.
        (new AddRedirectUrlsToGatewayData())($this->formDataFor($donation), $donation);

        $gatewayData = apply_filters(
            'givewp_create_payment_gateway_data_' . TestOffsiteGateway::id(),
            [],
            $donation
        );

        $command = (new TestOffsiteGateway())->createPayment($donation, $gatewayData);

        $this->assertInstanceOf(RedirectOffsite::class, $command);

        return $command->redirectUrl;
    }

    /**
     * The v3 form data the redirect-url listener reads, with the confirmation-page redirect turned off so
     * the success URL is the in-iframe receipt - the case that carries a query string of its own.
     */
    private function formDataFor(Donation $donation)
    {
        $formData = $this->getMockBuilder('Give\DonationForms\DataTransferObjects\DonateControllerData')
            ->disableOriginalConstructor()
            ->onlyMethods(['getSuccessUrl', 'getCancelUrl'])
            ->getMock();

        $formData->method('getSuccessUrl')->willReturn(
            home_url('/donation-confirmation/?givewp-event=donation-completed&givewp-receipt-id=' . $donation->purchaseKey)
        );
        $formData->method('getCancelUrl')->willReturn(home_url('/donate/'));

        return $formData;
    }
}

<?php

namespace Give\Tests\Feature\Gateways;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\DataTransferObjects\GatewayRouteData;
use Give\Framework\PaymentGateways\Routes\RouteSignature;
use Give\PaymentGateways\Gateways\TestOffsiteGateway\TestOffsiteGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Drives a real gateway through the secure route it generates.
 *
 * TestOffsiteGateway is the in-core example of a secureRouteMethod: createPayment() signs a return URL and
 * securelyReturnFromOffsiteRedirect() completes whichever donation givewp-donation-id names. That arg is
 * the one an attacker would edit, so these tests are written against it rather than against the signature
 * in isolation.
 *
 * @since TBD
 */
class SecureRouteArgsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function testAGeneratedRouteUrlValidates()
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        $request = $this->returnRequestFor($donation);

        $this->assertTrue($this->signatureFor($request)->isValid($request['give-route-signature']));
    }

    /**
     * The attack: a donor's own signed return URL, repointed at somebody else's pending donation, which
     * this gateway's route method would complete.
     *
     * @since TBD
     */
    public function testARouteUrlRepointedAtAnotherDonationIsRejected()
    {
        $mine = Donation::factory()->create(['status' => DonationStatus::PENDING()]);
        $theirs = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        $request = $this->returnRequestFor($mine);
        $request['givewp-donation-id'] = $theirs->id;

        $this->assertFalse($this->signatureFor($request)->isValid($request['give-route-signature']));
        $this->assertTrue(Donation::find($theirs->id)->status->isPending());
    }

    /**
     * The return URL rides on the same request and decides where the donor lands, so it has to be covered
     * as well or a signed URL becomes an open redirect.
     *
     * @since TBD
     */
    public function testAnEditedReturnUrlIsRejected()
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        $request = $this->returnRequestFor($donation);
        $request['givewp-return-url'] = 'https://not-this-site.example/';

        $this->assertFalse($this->signatureFor($request)->isValid($request['give-route-signature']));
    }

    /**
     * Offsite gateways append their own parameters to the return URL. Those were never signed, so they
     * must not invalidate a genuine return.
     *
     * @since TBD
     */
    public function testParametersTheGatewayAppendsAreIgnored()
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        $request = $this->returnRequestFor($donation);
        $request['transaction_id'] = 'offsite-txn-1';
        $request['status'] = 'completed';

        $this->assertTrue($this->signatureFor($request)->isValid($request['give-route-signature']));
    }

    /**
     * A gateway arg named after one of the route's own params would be signed, then overwritten on the
     * URL and excluded from queryParams on the way back — a URL that could never validate. Reserved
     * names are dropped before signing instead.
     *
     * @since TBD
     */
    public function testAReservedRouteParamPassedAsAnArgCannotBreakTheUrl()
    {
        $donation = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        $url = (new TestOffsiteGateway())->generateSecureGatewayRouteUrl(
            'securelyReturnFromOffsiteRedirect',
            $donation->id,
            [
                'givewp-donation-id' => $donation->id,
                'give-route-signature-id' => 'spoofed',
            ]
        );

        parse_str(parse_url($url, PHP_URL_QUERY), $request);
        $request = give_clean($request);

        $this->assertSame((string)$donation->id, $request['give-route-signature-id']);
        $this->assertTrue($this->signatureFor($request)->isValid($request['give-route-signature']));
    }

    /**
     * Runs the gateway's real createPayment() and turns the URL it redirects to back into a request.
     *
     * The successUrl is shaped like production hands it over: rawurlencoded by
     * AddRedirectUrlsToGatewayData, carrying the confirmation page's own query args. parse_str
     * urldecodes the way PHP does for $_GET, and give_clean is what GatewayRoute applies — so the
     * value comes back decoded, not as it was signed.
     */
    private function returnRequestFor(Donation $donation): array
    {
        $command = (new TestOffsiteGateway())->createPayment($donation, [
            'successUrl' => rawurlencode(
                home_url('/donation-confirmation/?givewp-event=donation-completed&givewp-receipt-id=3e714ece57ed9590ece70ac2c296b6d0')
            ),
        ]);

        $this->assertInstanceOf(RedirectOffsite::class, $command);

        parse_str(parse_url($command->redirectUrl, PHP_URL_QUERY), $request);

        return give_clean($request);
    }

    /**
     * The same call GatewayRoute::validateSignature() makes.
     */
    private function signatureFor(array $request): RouteSignature
    {
        return RouteSignature::fromRouteData(GatewayRouteData::fromRequest($request));
    }
}

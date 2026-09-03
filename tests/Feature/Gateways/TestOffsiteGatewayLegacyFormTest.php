<?php

namespace Give\Tests\Feature\Gateways;

use Give\Donations\Models\Donation;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\LegacyPaymentGateways\Adapters\LegacyPaymentGatewayRegisterAdapter;
use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Framework\PaymentGateways\Routes\GatewayRoute;
use Give\PaymentGateways\Gateways\TestOffsiteGateway\TestOffsiteGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\InterruptsRedirects;
use Give\Tests\TestTraits\RefreshDatabase;
use WPDieException;

/**
 * A legacy (v2) form donation paid through an offsite gateway, driven the way the browser drives it:
 * the form posts to give_process_donation_form(), the gateway redirects the donor offsite with a signed
 * return URL, and the processor sends the donor back to that URL, where GatewayRoute validates the
 * signature and completes the donation.
 *
 * TestOffsiteGateway is the in-core offsite gateway, so it stands in for the real ones. The legacy adapter
 * hands it the success URL raw, and under plain permalinks that URL carries a query string of its own, so
 * this is the shape of return URL that Razorpay and PayPal Standard produce on legacy forms.
 *
 * @since 4.16.8
 */
class TestOffsiteGatewayLegacyFormTest extends TestCase
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

        // Boot connects the legacy adapter to the gateways registered by then; this one was not among them.
        give(LegacyPaymentGatewayRegisterAdapter::class)->connectGatewayToLegacyPaymentGatewayAdapter(
            TestOffsiteGateway::class
        );

        give_update_option('gateways', [TestOffsiteGateway::id() => 1]);

        // The ampersand this covers exists only under plain permalinks, where the success page is ?page_id=N.
        update_option('permalink_structure', '');

        give_update_option('success_page', wp_insert_post([
            'post_type' => 'page',
            'post_status' => 'publish',
            'post_title' => 'Donation Confirmation',
        ]));
    }

    /**
     * @since 4.16.8
     */
    public function tearDown(): void
    {
        $_POST = [];
        $_GET = [];

        give_clear_errors();

        parent::tearDown();
    }

    /**
     * @since 4.16.8
     */
    public function testALegacyFormDonationCompletesWhenTheDonorReturnsFromTheGateway()
    {
        $offsiteUrl = $this->donateThroughLegacyForm($this->createLegacyForm());

        parse_str(parse_url($offsiteUrl, PHP_URL_QUERY), $_GET);

        $donation = Donation::find((int)$_GET['givewp-donation-id']);

        $this->assertTrue($donation->status->isPending());

        $receiptUrl = $this->captureRedirect(static function () {
            (new GatewayRoute())();
        });

        $this->assertSame(
            add_query_arg(['payment-confirmation' => TestOffsiteGateway::id()], give_get_success_page_url()),
            $receiptUrl
        );
        $this->assertStringContainsString('&payment-confirmation=', $receiptUrl);
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
        $offsiteUrl = $this->donateThroughLegacyForm($this->createLegacyForm());

        $theirs = Donation::factory()->create(['status' => DonationStatus::PENDING()]);

        parse_str(parse_url($offsiteUrl, PHP_URL_QUERY), $_GET);
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
     * Posts the legacy form the way the browser does and returns the URL the gateway sends the donor to.
     */
    private function donateThroughLegacyForm(int $formId): string
    {
        $_POST = [
            'give-form-id' => $formId,
            'give-form-hash' => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'give-form-title' => 'Legacy form',
            'give-form-id-prefix' => "{$formId}-1",
            'give-current-url' => home_url("/?post_type=give_forms&p={$formId}"),
            'give-form-minimum' => '1.00',
            'give-form-maximum' => '999999.99',
            'give-amount' => '10.00',
            'give-price-id' => '0',
            'give-gateway' => TestOffsiteGateway::id(),
            'payment-mode' => TestOffsiteGateway::id(),
            'give_first' => 'Ada',
            'give_last' => 'Lovelace',
            'give_email' => 'ada@example.test',
            'give_action' => 'purchase',
        ];

        $offsiteUrl = $this->captureRedirect(static function () {
            give_process_donation_form();
        });

        $this->assertSame([], give_get_errors() ?: [], 'The legacy form did not accept the donation.');
        $this->assertStringContainsString('give-listener=give-gateway', $offsiteUrl);

        return $offsiteUrl;
    }

    private function createLegacyForm(): int
    {
        $formId = wp_insert_post([
            'post_type' => 'give_forms',
            'post_status' => 'publish',
            'post_title' => 'Legacy form',
        ]);

        give_update_meta($formId, '_give_price_option', 'set');
        give_update_meta($formId, '_give_set_price', '10.00');

        return $formId;
    }
}

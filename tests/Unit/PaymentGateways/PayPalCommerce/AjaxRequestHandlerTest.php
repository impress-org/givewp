<?php

namespace Give\Tests\Unit\PaymentGateways\PayPalCommerce;

use Give\DonationForms\Models\DonationForm;
use Give\Donations\ValueObjects\DonationType;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\PaymentGateways\PayPalCommerce\AjaxRequestHandler;
use Give\PaymentGateways\PayPalCommerce\Repositories\PayPalOrder;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Helper_Form;
use PHPUnit\Framework\MockObject\MockObject;
use WPDieException;

/**
 * The frontend ajax endpoints are registered on wp_ajax_nopriv behind a page-rendered nonce, so
 * anything they refuse must be refused before PayPal is called. PayPalOrder is mocked; a call that
 * reaches it on a rejected request is the failure these tests exist to catch.
 *
 * @since TBD
 *
 * @covers \Give\PaymentGateways\PayPalCommerce\AjaxRequestHandler
 */
class AjaxRequestHandlerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var PayPalOrder|MockObject
     */
    private $payPalOrder;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        // wp_send_json_* calls wp_die() only when wp_doing_ajax() is true.
        add_filter('wp_doing_ajax', '__return_true');

        // Priority 11 so give_die()'s priority-10 handlers are overridden and wp_die() stays catchable.
        add_filter('wp_die_ajax_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_json_handler', [$this, 'get_wp_die_handler'], 11);
        add_filter('wp_die_handler', [$this, 'get_wp_die_handler'], 11);

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        $this->payPalOrder = $this->createMock(PayPalOrder::class);
        give()->instance(PayPalOrder::class, $this->payPalOrder);

        give_clear_errors();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        give()->forgetInstance(PayPalOrder::class);

        $_POST = [];
        $_GET = [];

        give_clear_errors();

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testCreateOrderCreatesPayPalOrderForValidV3Request(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(25);
        $_POST = $this->v3Request($form);

        $orderData = null;
        $this->payPalOrder->expects($this->once())
            ->method('createOrder')
            ->willReturnCallback(static function (array $data) use (&$orderData) {
                $orderData = $data;

                return 'ORDER123';
            });

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertTrue($response['success']);
        $this->assertSame('ORDER123', $response['data']['id']);
        $this->assertSame($form->id, $orderData['formId']);
        $this->assertSame('25', $orderData['donationAmount']);
    }

    /**
     * @since TBD
     */
    public function testCreateOrderRejectsV3AmountBelowFormMinimumBeforeCallingPayPal(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(25);
        $_POST = $this->v3Request($form, ['amount' => '0.5', 'give-amount' => '0.5']);

        $this->payPalOrder->expects($this->never())->method('createOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('25', $response['data']['error']);
    }

    /**
     * The total PayPal charges ("give-amount", fee recovery included) can never be less than the
     * amount the form validated.
     *
     * @since TBD
     */
    public function testCreateOrderRejectsV3TotalBelowValidatedAmount(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(1);
        $_POST = $this->v3Request($form, ['amount' => '25', 'give-amount' => '0.5']);

        $this->payPalOrder->expects($this->never())->method('createOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertFalse($response['success']);
        $this->assertSame('Invalid donation amount.', $response['data']['error']);
    }

    /**
     * The form layer only validates fields present in the request, so the handler itself has to
     * insist the validated amount is there at all.
     *
     * @since TBD
     */
    public function testCreateOrderRejectsV3RequestWithoutValidatedAmount(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(25);
        $_POST = $this->v3Request($form);
        unset($_POST['amount']);

        $this->payPalOrder->expects($this->never())->method('createOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertFalse($response['success']);
        $this->assertSame('Invalid donation amount.', $response['data']['error']);
    }

    /**
     * Posting the complete form values means the honeypot rule runs on this endpoint too.
     *
     * @since TBD
     */
    public function testCreateOrderRejectsV3RequestWithFilledHoneypot(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(1);
        $_POST = $this->v3Request($form, ['donationBirthday' => '1970-01-01']);

        $this->payPalOrder->expects($this->never())->method('createOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertFalse($response['success']);
    }

    /**
     * @since TBD
     */
    public function testCreateOrderRejectsV2AmountBelowFormMinimumBeforeCallingPayPal(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(25);
        $_POST = $this->v2Request($formId, ['give-amount' => '0.5']);

        $this->payPalOrder->expects($this->never())->method('createOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('minimum donation amount', $response['data']['error']);
    }

    /**
     * The v2 form posts its PayPal-hosted card inputs (empty on the smart-buttons path); the legacy
     * validator must not require them on this endpoint.
     *
     * @since TBD
     */
    public function testCreateOrderDoesNotRequireLocalCardFieldsForV2Forms(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(1);
        $_POST = $this->v2Request($formId, ['card_name' => '']);

        $this->payPalOrder->expects($this->once())->method('createOrder')->willReturn('ORDER123');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertTrue($response['success']);

        $requiredFields = apply_filters(
            'give_donation_form_required_fields',
            ['give_email' => [], 'card_name' => [], 'card_number' => [], 'card_cvc' => [], 'card_expiry' => []],
            $formId
        );

        $this->assertSame(['give_email'], array_keys($requiredFields));
    }

    /**
     * The amount that reaches PayPal for a v2 form is the post-filter total (fee recovery), and it
     * is held to the form's maximum even when the raw posted amount is within it.
     *
     * @since TBD
     */
    public function testCreateOrderRejectsV2FilteredTotalAboveFormMaximum(): void
    {
        $formId = Give_Helper_Form::create_simple_form([
            'meta' => [
                '_give_custom_amount' => 'enabled',
                '_give_custom_amount_range_minimum' => '1',
                '_give_custom_amount_range_maximum' => '100',
            ],
        ])->get_ID();
        $_POST = $this->v2Request($formId, ['give-amount' => '98']);

        add_filter('give_donation_total', static function ($amount) {
            return $amount + 5;
        });

        $this->payPalOrder->expects($this->never())->method('createOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->createOrder();
        });

        $this->assertFalse($response['success']);
        $this->assertStringContainsString('must not exceed', $response['data']['error']);
    }

    /**
     * @since TBD
     */
    public function testCreateOrderRejectsRequestWithInvalidNonce(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(1);
        $_POST = $this->v3Request($form, ['give-form-hash' => 'not-a-nonce']);

        $this->payPalOrder->expects($this->never())->method('createOrder');

        $this->expectException(WPDieException::class);

        give(AjaxRequestHandler::class)->createOrder();
    }

    /**
     * v3 forms never call this endpoint; their capture happens in PayPalCommerce::createPayment().
     *
     * @since TBD
     */
    public function testApproveOrderRefusesV3FormsWithoutCapturing(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(1);
        $_POST = $this->v3Request($form);
        $_GET = ['order' => 'ORDER123', 'update_amount' => '0'];

        $this->payPalOrder->expects($this->never())->method('approveOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->approveOrder();
        });

        $this->assertFalse($response['success']);
    }

    /**
     * @since TBD
     */
    public function testUpdateOrderAmountRefusesV3Forms(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(1);
        $_POST = $this->v3Request($form);
        $_GET = ['order' => 'ORDER123'];

        $this->payPalOrder->expects($this->never())->method('updateOrderAmount');
        $this->payPalOrder->expects($this->never())->method('getApprovedOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->updateOrderAmount();
        });

        $this->assertFalse($response['success']);
    }

    /**
     * v2 approve requests are validated like create requests, even when the amount did not change.
     *
     * @since TBD
     */
    public function testApproveOrderRejectsV2AmountBelowFormMinimumBeforeCapturing(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(25);
        $_POST = $this->v2Request($formId, ['give-amount' => '0.5']);
        $_GET = ['order' => 'ORDER123', 'update_amount' => '0'];

        $this->payPalOrder->expects($this->never())->method('approveOrder');

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->approveOrder();
        });

        $this->assertFalse($response['success']);
    }

    /**
     * Option-based (v2) forms capture through this endpoint, before the donation record exists;
     * their scripts call it right after PayPal's onApprove and before the form is submitted.
     *
     * @since TBD
     */
    public function testApproveOrderStillCapturesV2Orders(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(1);
        $_POST = $this->v2Request($formId);
        $_GET = ['order' => 'ORDER123', 'update_amount' => '0'];

        $capture = (object)['status' => 'COMPLETED'];
        $order = (object)[
            'id' => 'ORDER123',
            'purchase_units' => [(object)['payments' => (object)['captures' => [$capture]]]],
        ];

        $this->payPalOrder->expects($this->once())
            ->method('approveOrder')
            ->with('ORDER123')
            ->willReturn($order);

        $response = $this->invokeAndCatchWpDie(function () {
            give(AjaxRequestHandler::class)->approveOrder();
        });

        $this->assertTrue($response['success']);
        $this->assertSame('ORDER123', $response['data']['order']['id']);
    }

    /**
     * @since TBD
     */
    private function createV3FormWithCustomAmountMinimum(int $minimum): DonationForm
    {
        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $form->blocks->findByName('givewp/donation-amount')
            ->setAttribute('customAmount', true)
            ->setAttribute('customAmountMin', $minimum);

        $form->save();

        return $form;
    }

    /**
     * @since TBD
     */
    private function createV2FormWithCustomAmountMinimum(int $minimum): int
    {
        return Give_Helper_Form::create_simple_form([
            'meta' => [
                '_give_custom_amount' => 'enabled',
                '_give_custom_amount_range_minimum' => (string)$minimum,
            ],
        ])->get_ID();
    }

    /**
     * What the v3 gateway client posts: its own keys plus the complete form values.
     *
     * @since TBD
     */
    private function v3Request(DonationForm $form, array $overrides = []): array
    {
        return array_merge([
            'give-form-id' => $form->id,
            'give-form-hash' => wp_create_nonce("give_donation_form_nonce_{$form->id}"),
            'give-form-title' => $form->title,
            'give-amount' => '25',
            'give_first' => 'Bill',
            'give_last' => 'Murray',
            'give_email' => 'billmurray@givewp.com',
            'formId' => $form->id,
            'gatewayId' => TestGateway::id(),
            'amount' => '25',
            'currency' => 'USD',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billmurray@givewp.com',
            'donationType' => DonationType::SINGLE()->getValue(),
            'donationBirthday' => '',
        ], $overrides);
    }

    /**
     * What the v2 gateway client posts: the whole legacy form.
     *
     * @since TBD
     */
    private function v2Request(int $formId, array $overrides = []): array
    {
        return array_merge([
            'give-form-id' => $formId,
            'give-form-hash' => wp_create_nonce("give_donation_form_nonce_{$formId}"),
            'give-form-title' => 'Test Donation Form',
            'give-gateway' => TestGateway::id(),
            'give-amount' => '25',
            'give_first' => 'Bill',
            'give_last' => 'Murray',
            'give_email' => 'billmurray@givewp.com',
            // Rendered by the v2 PayPal card form and posted empty on the smart-buttons path.
            'card_name' => '',
        ], $overrides);
    }

    /**
     * Runs an ajax handler and returns its decoded JSON response. wp_send_json_* ends in wp_die(),
     * which the test die handler turns into a WPDieException.
     *
     * @since TBD
     */
    private function invokeAndCatchWpDie(callable $callable): array
    {
        ob_start();

        try {
            $callable();
            $output = ob_get_clean();
            $this->fail("Expected WPDieException was not thrown. Output: {$output}");
        } catch (WPDieException $exception) {
            $output = ob_get_clean();

            if (empty($output)) {
                $output = $exception->getMessage();
            }

            /*
             * Keep only the first JSON payload. The handlers' catch-all blocks catch the
             * WPDieException raised by a successful wp_send_json_success() and emit a second,
             * error payload before the exception escapes; in production wp_die() has already
             * exited by then.
             */
            $start = strpos($output, '{"success"');
            $output = false === $start ? $output : substr($output, $start);

            $next = strpos($output, '{"success"', 1);
            if (false !== $next) {
                $output = substr($output, 0, $next);
            }

            $jsonEnd = strpos($output, "\nwp_die()");
            if (false !== $jsonEnd) {
                $output = substr($output, 0, $jsonEnd);
            }

            $response = json_decode(trim($output), true);

            if (!is_array($response)) {
                $this->fail("Handler output is not valid JSON: \"{$output}\"");
            }

            return $response;
        }
    }
}

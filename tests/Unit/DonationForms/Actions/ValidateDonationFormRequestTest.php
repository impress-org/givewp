<?php

namespace Give\Tests\Unit\DonationForms\Actions;

use Give\DonationForms\Actions\ValidateDonationFormRequest;
use Give\DonationForms\Exceptions\DonationFormFieldErrorsException;
use Give\DonationForms\Exceptions\DonationFormForbidden;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\ValueObjects\DonationFormStatus;
use Give\Donations\ValueObjects\DonationType;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use Give_Helper_Form;

/**
 * @since TBD
 *
 * @covers \Give\DonationForms\Actions\ValidateDonationFormRequest
 */
class ValidateDonationFormRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        add_filter('give_get_option_gateways', static function ($gateways) {
            return array_merge($gateways, [TestGateway::id() => true]);
        });

        add_filter('give_default_gateway', static function () {
            return TestGateway::id();
        });

        give_clear_errors();
    }

    /**
     * @since TBD
     */
    public function tearDown(): void
    {
        $_POST = [];

        give_clear_errors();

        parent::tearDown();
    }

    /**
     * @since TBD
     */
    public function testV3FormRequestWithinRulesPasses(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(25);

        $this->expectNotToPerformAssertions();

        (new ValidateDonationFormRequest())($form->id, $this->v3Request(25));
    }

    /**
     * @since TBD
     */
    public function testV3FormRequestBelowCustomAmountMinimumIsRejected(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(25);

        $this->expectException(DonationFormFieldErrorsException::class);

        (new ValidateDonationFormRequest())($form->id, $this->v3Request(0.5));
    }

    /**
     * The form id the caller verified wins over whatever the request claims, so a request cannot be
     * validated against a more permissive form than the one it is for.
     *
     * @since TBD
     */
    public function testV3FormRequestIsValidatedAgainstTheGivenFormNotTheRequestFormId(): void
    {
        $strictForm = $this->createV3FormWithCustomAmountMinimum(25);
        $lenientForm = $this->createV3FormWithCustomAmountMinimum(1);

        $request = $this->v3Request(5);
        $request['formId'] = $lenientForm->id;

        $this->expectException(DonationFormFieldErrorsException::class);

        (new ValidateDonationFormRequest())($strictForm->id, $request);
    }

    /**
     * @since TBD
     */
    public function testV3TrashedFormIsForbidden(): void
    {
        $form = $this->createV3FormWithCustomAmountMinimum(1);
        $form->status = DonationFormStatus::TRASH();
        $form->save();

        $this->expectException(DonationFormForbidden::class);

        (new ValidateDonationFormRequest())($form->id, $this->v3Request(25));
    }

    /**
     * @since TBD
     */
    public function testV2FormRequestWithinRulesPasses(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(25);
        $_POST = $this->v2Request($formId, 25);

        (new ValidateDonationFormRequest())($formId, $_POST);

        $this->assertEmpty(give_get_errors());
    }

    /**
     * @since TBD
     */
    public function testV2FormRequestIsRejectedWhenThePostedFormIdDoesNotMatch(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(1);
        $otherFormId = $this->createV2FormWithCustomAmountMinimum(1);
        $_POST = $this->v2Request($otherFormId, 25);

        try {
            (new ValidateDonationFormRequest())($formId, $_POST);
            $this->fail('Expected DonationFormFieldErrorsException');
        } catch (DonationFormFieldErrorsException $exception) {
            $this->assertSame(['give_invalid_donation_form'], $exception->getError()->get_error_codes());
        }
    }

    /**
     * Add-on rules and the legacy level check hang off give_checkout_error_checks, so the v2 branch
     * has to fire it the way give_process_donation_form() does.
     *
     * @since TBD
     */
    public function testV2FormRequestFiresCheckoutErrorChecks(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(1);
        $_POST = $this->v2Request($formId, 25);

        $validData = null;
        add_action('give_checkout_error_checks', static function ($data) use (&$validData) {
            $validData = $data;
        });

        (new ValidateDonationFormRequest())($formId, $_POST);

        $this->assertIsArray($validData);
        $this->assertSame(TestGateway::id(), $validData['gateway']);
    }

    /**
     * @since TBD
     */
    public function testV2FormRequestBelowMinimumIsRejectedAndSessionErrorsAreCleared(): void
    {
        $formId = $this->createV2FormWithCustomAmountMinimum(25);
        $_POST = $this->v2Request($formId, 0.5);

        try {
            (new ValidateDonationFormRequest())($formId, $_POST);
            $this->fail('Expected DonationFormFieldErrorsException');
        } catch (DonationFormFieldErrorsException $exception) {
            $this->assertContains('invalid_donation_minimum', $exception->getError()->get_error_codes());
            $this->assertEmpty(give_get_errors());
        }
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
     * @since TBD
     */
    private function v3Request(float $amount): array
    {
        return [
            'gatewayId' => TestGateway::id(),
            'amount' => $amount,
            'currency' => 'USD',
            'firstName' => 'Bill',
            'lastName' => 'Murray',
            'email' => 'billmurray@givewp.com',
            'donationType' => DonationType::SINGLE()->getValue(),
        ];
    }

    /**
     * @since TBD
     */
    private function v2Request(int $formId, float $amount): array
    {
        return [
            'give-form-id' => $formId,
            'give-gateway' => TestGateway::id(),
            'give-amount' => (string)$amount,
            'give_first' => 'Bill',
            'give_last' => 'Murray',
            'give_email' => 'billmurray@givewp.com',
            // Rendered by the v2 PayPal card form and posted empty on the smart-buttons path.
            'card_name' => '',
        ];
    }
}

<?php

namespace Give\Tests\Unit\PaymentGateways\Gateways\TestGateway\Actions;

use Closure;
use Give\DonationForms\Models\DonationForm;
use Give\DonationForms\Repositories\DonationFormRepository;
use Give\DonationForms\Rules\GatewayRule;
use Give\PaymentGateways\Gateways\Offline\OfflineGateway;
use Give\PaymentGateways\Gateways\TestGateway\Actions\DisableTestGatewayInLiveMode;
use Give\PaymentGateways\Gateways\TestGateway\TestGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * @since TBD
 *
 * @covers \Give\PaymentGateways\Gateways\TestGateway\Actions\DisableTestGatewayInLiveMode
 */
class DisableTestGatewayInLiveModeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @since TBD
     */
    public function setUp(): void
    {
        parent::setUp();

        $enabledGateways = [TestGateway::id() => 1, OfflineGateway::id() => 1];

        give_update_option('gateways', $enabledGateways);
        give_update_option('gateways_v3', $enabledGateways);
    }

    /**
     * @since TBD
     */
    public function testRemovesTestGatewayWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');

        $gateways = (new DisableTestGatewayInLiveMode())([TestGateway::id() => [], OfflineGateway::id() => []]);

        $this->assertSame([OfflineGateway::id()], array_keys($gateways));
    }

    /**
     * @since TBD
     */
    public function testKeepsTestGatewayWhileTestModeIsEnabled(): void
    {
        add_filter('give_is_test_mode', '__return_true');

        $input = [TestGateway::id() => [], OfflineGateway::id() => []];

        $this->assertSame($input, (new DisableTestGatewayInLiveMode())($input));
    }

    /**
     * @since TBD
     */
    public function testV2EnabledGatewaysExcludeTestGatewayWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');

        $gateways = give_get_enabled_payment_gateways(0, 2);

        $this->assertArrayNotHasKey(TestGateway::id(), $gateways);
        $this->assertArrayHasKey(OfflineGateway::id(), $gateways);
        $this->assertFalse(give_is_gateway_active(TestGateway::id()));
    }

    /**
     * @since TBD
     */
    public function testV2EnabledGatewaysIncludeTestGatewayWhileTestModeIsEnabled(): void
    {
        add_filter('give_is_test_mode', '__return_true');

        $this->assertArrayHasKey(TestGateway::id(), give_get_enabled_payment_gateways(0, 2));
        $this->assertTrue(give_is_gateway_active(TestGateway::id()));
    }

    /**
     * @since TBD
     */
    public function testV3EnabledGatewaysExcludeTestGatewayWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $gateways = give(DonationFormRepository::class)->getEnabledPaymentGateways($form->id);

        $this->assertArrayNotHasKey(TestGateway::id(), $gateways);
        $this->assertArrayHasKey(OfflineGateway::id(), $gateways);
    }

    /**
     * @since TBD
     */
    public function testV3EnabledGatewaysIncludeTestGatewayWhileTestModeIsEnabled(): void
    {
        add_filter('give_is_test_mode', '__return_true');

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $this->assertArrayHasKey(
            TestGateway::id(),
            give(DonationFormRepository::class)->getEnabledPaymentGateways($form->id)
        );
    }

    /**
     * @since TBD
     */
    public function testV3GatewayRuleRejectsTestGatewayWhileTestModeIsDisabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();
        $failed = false;

        (new GatewayRule())(
            TestGateway::id(),
            Closure::fromCallable(static function () use (&$failed) {
                $failed = true;
            }),
            'gatewayId',
            ['formId' => $form->id]
        );

        $this->assertTrue($failed);
    }

    /**
     * @since TBD
     */
    public function testV3DefaultGatewayFallsBackWhenTheTestGatewayIsTheOnlyOneEnabled(): void
    {
        add_filter('give_is_test_mode', '__return_false');
        give_update_option('gateways_v3', [TestGateway::id() => 1]);

        /** @var DonationForm $form */
        $form = DonationForm::factory()->create();

        $this->assertSame([], give(DonationFormRepository::class)->getEnabledPaymentGateways($form->id));
        $this->assertSame('', give(DonationFormRepository::class)->getDefaultEnabledGatewayId($form->id));
    }
}

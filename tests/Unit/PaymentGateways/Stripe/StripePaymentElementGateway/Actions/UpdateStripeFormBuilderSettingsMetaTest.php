<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe\StripePaymentElementGateway\Actions;

use Closure;
use Exception;
use Give\Vendors\Faker\Factory;
use Give\DonationForms\Models\DonationForm;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\Actions\UpdateStripeFormBuilderSettingsMeta;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use ReflectionClass;
use ReflectionException;

class UpdateStripeFormBuilderSettingsMetaTest extends TestCase
{

    use RefreshDatabase;

    public static function SetUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::seedStripeAccountsOptions();
    }

    /**
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testDonationFormWithoutPaymentGatewaysBlockDoesNotUpdateMeta()
    {
        $form = DonationForm::factory()->create();
        $form->blocks->remove('givewp/payment-gateways');
        $form->save();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = [
            'perFormAccounts' => '',
            'defaultAccount' => '',
        ];
        $actual = [
            'perFormAccounts' => give()->form_meta->get_meta($form->id, 'give_stripe_per_form_accounts', true),
            'defaultAccount' => give()->form_meta->get_meta($form->id, '_give_stripe_default_account', true),
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testDonationFormWithInvalidStripAttributesDoesNotUpdateMeta()
    {
        $form = DonationForm::factory()->create();
        $block = $form->blocks->findByName('givewp/payment-gateways');
        $block->setAttribute('stripeUseGlobalDefault', 'invalid-value');
        $block->setAttribute('stripeAccountId', 123);
        $form->save();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = [
            'perFormAccounts' => '',
            'defaultAccount' => '',
        ];
        $actual = [
            'perFormAccounts' => give()->form_meta->get_meta($form->id, 'give_stripe_per_form_accounts', true),
            'defaultAccount' => give()->form_meta->get_meta($form->id, '_give_stripe_default_account', true),
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @since 3.0.0
     *
     * @throws Exception
     */
    public function testDonationFormWithValidStripeAttributesDoesUpdateMeta()
    {
        $form = DonationForm::factory()->create();
        $block = $form->blocks->findByName('givewp/payment-gateways');
        $block->setAttribute('stripeUseGlobalDefault', false);
        $block->setAttribute('stripeAccountId', 'acct_123456789012345');
        $form->save();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = [
            'perFormAccounts' => 'enabled',
            'defaultAccount' => 'acct_123456789012345',
        ];
        $actual = [
            'perFormAccounts' => give()->form_meta->get_meta($form->id, 'give_stripe_per_form_accounts', true),
            'defaultAccount' => give()->form_meta->get_meta($form->id, '_give_stripe_default_account', true),
        ];

        $this->assertSame($expected, $actual);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testDonationFormWithoutPaymentGatewaysBlockReturnsDefaultAccount()
    {
        $form = DonationForm::factory()->create();
        $form->blocks->remove('givewp/payment-gateways');
        $form->save();
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = (give_stripe_get_default_account())['account_id'];
        $actual = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($expected, $actual);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPaymentGatewaysBlockConfiguredToUseGlobalDefaultReturnsDefaultAccount()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        $form = DonationForm::factory()->create();
        $block = $form->blocks->findByName('givewp/payment-gateways');
        $block->setAttribute('stripeUseGlobalDefault', true);
        $form->save();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = (give_stripe_get_default_account())['account_id'];
        $actual = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($expected, $actual);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPaymentGatewaysBlockConfiguredToAnEmptyAccountIdReturnsEmptyAccountId()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        $form = DonationForm::factory()->create();
        $block = $form->blocks->findByName('givewp/payment-gateways');
        $block->setAttribute('stripeUseGlobalDefault', false);
        $block->setAttribute('stripeAccountId', '');
        $form->save();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = '';
        $actual = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($expected, $actual);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPaymentGatewaysBlockConfiguredToAnInvalidAccountIdReturnsEmptyAccountId()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        $form = DonationForm::factory()->create();
        $block = $form->blocks->findByName('givewp/payment-gateways');
        $block->setAttribute('stripeUseGlobalDefault', false);
        $block->setAttribute('stripeAccountId', 'invalid-account-id');
        $form->save();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = '';
        $actual = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($expected, $actual);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPaymentGatewaysBlockConfiguredToAValidAccountIdReturnsThatAccountId()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        $allAccounts = array_keys(give_stripe_get_all_accounts());
        $selectedAccount = end($allAccounts);
        $form = DonationForm::factory()->create();
        $block = $form->blocks->findByName('givewp/payment-gateways');
        $block->setAttribute('stripeUseGlobalDefault', false);
        $block->setAttribute('stripeAccountId', $selectedAccount);
        $form->save();

        (new UpdateStripeFormBuilderSettingsMeta())($form);

        $expected = $selectedAccount;
        $actual = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($expected, $actual);
    }

    private static function seedStripeAccountsOptions()
    {
        $faker = Factory::create();
        $accounts = [];

        for ($i = 1; $i <= 3; $i++) {
            $accountId = 'acct_' . $faker->regexify('[A-Za-z0-9]{15}');
            $accountName = $faker->company();

            $accounts[$accountId] = [
                "type" => "connect",
                "account_id" => $accountId,
                "account_slug" => $accountId,
                "account_name" => $accountName,
                "account_country" => $faker->countryCode(),
                "account_email" => $faker->email(),
                "live_secret_key" => 'sk_live_' . $faker->regexify('[A-Za-z0-9]{80}'),
                "test_secret_key" => 'sk_test_' . $faker->regexify('[A-Za-z0-9]{80}'),
                "live_publishable_key" => 'pk_live_' . $faker->regexify('[A-Za-z0-9]{80}'),
                "test_publishable_key" => 'pk_test_' . $faker->regexify('[A-Za-z0-9]{80}'),
                "statement_descriptor" => strtoupper($accountName),
            ];
        }

        give_update_option('_give_stripe_get_all_accounts', $accounts);
        give_update_option('_give_stripe_default_account', current(array_keys($accounts)));
    }

    /**
     * @return Closure
     * @throws ReflectionException
     */
    protected function getReflectedMethod(): Closure
    {
        $object = new StripePaymentElementGateway();
        $method = (new ReflectionClass($object))->getMethod('getStripeConnectedAccountKey');
        $method->setAccessible(true);

        return function (int $formId) use ($object, $method) {
            return $method->invokeArgs($object, [$formId]);
        };
    }
}

<?php

namespace Give\Tests\Feature\Gateways\Stripe;

use Closure;
use Exception;
use Faker\Factory;
use Give\DonationForms\Models\DonationForm;
use Give\PaymentGateways\Gateways\Stripe\StripePaymentElementGateway\StripePaymentElementGateway;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;
use ReflectionClass;
use ReflectionException;

class GetStripeConnectedAccountKeyTest extends TestCase
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
     * @throws ReflectionException
     */
    public function testDonationFormWithoutPaymentGatewaysBlockReturnsDefaultAccount()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();
        $defaultAccount = (give_stripe_get_default_account())['account_id'];

        $result = $getStripeConnectedAccountKey(0);

        $this->assertSame($defaultAccount, $result);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPaymentGatewaysBlockWithoutStripeSettingsReturnsDefaultAccount()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        $form = DonationForm::factory()->create();
        $defaultAccount = (give_stripe_get_default_account())['account_id'];

        $result = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($defaultAccount, $result);
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
        $blocks = $form->blocks;
        $paymentGatewaysBlock = $blocks->findByName('givewp/payment-gateways');
        $paymentGatewaysBlock->setAttribute('gatewaysSettings', [
            'stripe_payment_element' => [
                'useGlobalDefault' => true,
            ],
        ]);
        $form->save();
        $defaultAccount = (give_stripe_get_default_account())['account_id'];

        $result = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($defaultAccount, $result);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPaymentGatewaysBlockNotConfiguredToUseGlobalDefaultButWithoutAnAccountIdReturnsDefaultAccount()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        $form = DonationForm::factory()->create();
        $blocks = $form->blocks;
        $paymentGatewaysBlock = $blocks->findByName('givewp/payment-gateways');
        $paymentGatewaysBlock->setAttribute('gatewaysSettings', [
            'stripe_payment_element' => [
                'useGlobalDefault' => false,
            ],
        ]);
        $form->save();
        $defaultAccount = (give_stripe_get_default_account())['account_id'];

        $result = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($defaultAccount, $result);
    }

    /**
     * @since 3.0.0
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function testPaymentGatewaysBlockConfiguredToAnInvalidAccountIdReturnsDefaultAccount()
    {
        $getStripeConnectedAccountKey = $this->getReflectedMethod();

        $form = DonationForm::factory()->create();
        $blocks = $form->blocks;
        $paymentGatewaysBlock = $blocks->findByName('givewp/payment-gateways');
        $paymentGatewaysBlock->setAttribute('gatewaysSettings', [
            'stripe_payment_element' => [
                'useGlobalDefault' => false,
                'accountId' => 'invalid-account-id',
            ],
        ]);
        $form->save();
        $defaultAccount = (give_stripe_get_default_account())['account_id'];

        $result = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($defaultAccount, $result);
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
        $blocks = $form->blocks;
        $paymentGatewaysBlock = $blocks->findByName('givewp/payment-gateways');
        $paymentGatewaysBlock->setAttribute('gatewaysSettings', [
            'stripe_payment_element' => [
                'useGlobalDefault' => false,
                'accountId' => $selectedAccount,
            ],
        ]);
        $form->save();
        $defaultAccount = (give_stripe_get_default_account())['account_id'];

        $result = $getStripeConnectedAccountKey($form->id);

        $this->assertSame($selectedAccount, $result);
        $this->assertNotSame($defaultAccount, $result);
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

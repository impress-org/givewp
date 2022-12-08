<?php

use Give\Donations\Models\Donation;
use Give\Framework\Database\DB;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\Commands\PaymentProcessing;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;

/**
 * @since 2.19.0
 */
class CreditCardGatewayTest extends \Give\Tests\TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->setUpStripeAccounts();

        $this->form = Give_Helper_Form::create_simple_form();
        $_POST['give-form-id'] = $this->form->get_ID();
    }

    /**
     * @since 2.22.0
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        $donationsTable = DB::prefix('posts');
        $donationMetaTable = DB::prefix('give_donationmeta');
        $donorTable = DB::prefix('give_donors');
        $donorMetaTable = DB::prefix('give_donormeta');
        $subscriptionsTable = DB::prefix('give_subscriptions');
        $sequentialOrderingTable = DB::prefix('give_sequential_ordering');
        $notesTable = DB::prefix('give_comments');

        DB::query("TRUNCATE TABLE $donorTable");
        DB::query("TRUNCATE TABLE $donorMetaTable");
        DB::query("TRUNCATE TABLE $donationMetaTable");
        DB::query("TRUNCATE TABLE $donationsTable");
        DB::query("TRUNCATE TABLE $subscriptionsTable");
        DB::query("TRUNCATE TABLE $sequentialOrderingTable");
        DB::query("TRUNCATE TABLE $notesTable");
    }

    /** @test */
    public function it_creates_a_payment_that_is_complete()
    {
        $gateway = new CreditCardGateway();

        $this->mock(Give_Stripe_Payment_Intent::class, function () {
            return new Give_Stripe_Payment_Intent('succeeded');
        });

        $this->assertInstanceOf(
            PaymentComplete::class,
            $gateway->createPayment(
                $this->getDonationModel(),
                [ 'stripePaymentMethod' => new PaymentMethod('pm_1234') ]
            )
        );
    }

    /** @test */
    public function it_creates_a_payment_that_is_processing()
    {
        $gateway = new CreditCardGateway();

        $this->mock(Give_Stripe_Payment_Intent::class, function () {
            return new Give_Stripe_Payment_Intent('processing');
        });

        $this->assertInstanceOf(
            PaymentProcessing::class,
            $gateway->createPayment(
                $this->getDonationModel(),
                [ 'stripePaymentMethod' => new PaymentMethod('pm_1234') ]
            )
        );
    }

    /** @test */
    public function it_creates_a_payment_that_requires_action()
    {
        $_POST['give_stripe_payment_method'] = 'pm_1234';
        $gateway = new CreditCardGateway();

        $this->mock(Give_Stripe_Payment_Intent::class, function () {
            return new Give_Stripe_Payment_Intent('requires_action');
        });

        $this->assertInstanceOf(
            RedirectOffsite::class,
            $gateway->createPayment(
                $this->getDonationModel(),
                [ 'stripePaymentMethod' => new PaymentMethod('pm_1234') ]
            )
        );
    }

    /**
     * @throws Exception
     */
    public function getDonationModel(): Donation
    {
        return Donation::factory()->create();
    }

    /**
     * @return void
     */
    private function setUpStripeAccounts()
    {
        give_update_option(
            '_give_stripe_get_all_accounts',
            [
                'account_1' => [
                    'type' => 'manual',
                    'account_name' => 'Account 1',
                    'account_slug' => 'account_1',
                    'account_email' => '',
                    'account_country' => 'BR',
                    'account_id' => 'account_1',
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name'),
                ],
                'account_2' => [
                    'type' => 'manual',
                    'account_name' => 'Account 2',
                    'account_slug' => 'account_2',
                    'account_email' => '',
                    'account_country' => 'US',
                    'account_id' => 'account_2',
                    'live_secret_key' => 'dummy',
                    'test_secret_key' => 'dummy',
                    'live_publishable_key' => 'dummy',
                    'test_publishable_key' => 'dummy',
                    'statement_descriptor' => get_bloginfo('name'),
                ],
            ]
        );

        // Set default account globally.
        give_update_option('_give_stripe_default_account', 'account_1');
    }
}

class Give_Stripe_Customer
{
    public function get_id()
    {
        return 'cust_1234';
    }
}

class Give_Stripe_Payment_Intent
{
    protected $status;

    public function __construct($status)
    {
        $this->status = $status;
    }

    public function create()
    {
        return json_decode(
            json_encode([
                'id' => 'pi_1234',
                'status' => $this->status,
                'client_secret' => 'pi_secret',
                'next_action' => [
                    'redirect_to_url' => [
                        'url' => '',
                    ]
                ]
            ])
        );
    }
}

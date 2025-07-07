<?php

namespace Give\Tests\Unit\PaymentGateways\Stripe;

use Give\PaymentGateways\Stripe\ApplicationFee;
use Give\PaymentGateways\Stripe\Repositories\AccountDetail as AccountDetailRepository;
use Give\Tests\TestCase;
use Give\Tests\TestTraits\RefreshDatabase;

/**
 * Class ApplicationFeeTest
 */
final class ApplicationFeeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var AccountDetailRepository
     */
    private $repository;

    /**
     * @var ApplicationFee
     */
    private $gate;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpStripeAccounts();
        $this->repository = new AccountDetailRepository();
        $this->gate = new ApplicationFee($this->repository->getAccountDetail('account_br'));
    }

    private function setUpStripeAccounts()
    {
        $accounts = [];
        $countries = $this->unsupportedCountriesProvider();
        $countries['United States'] = ['account_us', 'US'];

        foreach ($countries as $countryName => $data) {
            [$accountSlug, $countryCode] = $data;

            $accounts[$accountSlug] = [
                'type' => 'manual',
                'account_name' => $countryName . ' Account',
                'account_slug' => $accountSlug,
                'account_email' => 'dummy@example.com',
                'account_country' => $countryCode,
                'account_id' => $accountSlug,
                'live_secret_key' => 'dummy',
                'test_secret_key' => 'dummy',
                'live_publishable_key' => 'dummy',
                'test_publishable_key' => 'dummy',
                'statement_descriptor' => get_bloginfo('name'),
            ];
        }

        give_update_option(
            '_give_stripe_get_all_accounts',
            $accounts
        );
    }

    /**
     * @dataProvider unsupportedCountriesProvider
     */
    public function testCanNotAddFeeIfMerchantCountryIsUnsupported(string $accountSlug, string $countryCode)
    {
        $applicationFee = new ApplicationFee($this->repository->getAccountDetail($accountSlug));
        $this->assertFalse(
            $applicationFee->doesCountrySupportApplicationFee(),
            "Country {$countryCode} should not support application fees"
        );
    }

    public function testCanAddFeeIfMerchantCountryIsUS()
    {
        give()->singleton(ApplicationFee::class, function () {
            return new ApplicationFee($this->repository->getAccountDetail('account_us'));
        });

        $this->assertTrue(
            ApplicationFee::canAddFee()
        );
    }

    public function testIsCountryNotSupportApplicationFee()
    {
        $this->assertFalse(
            $this->gate->doesCountrySupportApplicationFee()
        );
    }

    public function testIsCountrySupportApplicationFee()
    {
        $applicationFee = new ApplicationFee($this->repository->getAccountDetail('account_us'));
        $this->assertTrue(
            $applicationFee->doesCountrySupportApplicationFee()
        );
    }

    public function unsupportedCountriesProvider(): array
    {
        return [
            'Brazil' => ['account_br', 'BR'],
            'India' => ['account_in', 'IN'],
            'Malaysia' => ['account_my', 'MY'],
            'Mexico' => ['account_mx', 'MX'],
            'Singapore' => ['account_sg', 'SG'],
            'Thailand' => ['account_th', 'TH'],
        ];
    }
}

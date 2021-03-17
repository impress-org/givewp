<?php

use PHPUnit\Framework\TestCase;
use Give\PaymentGateways\Stripe\Repositories;

class AccountDetailTest extends TestCase{
	/**
	 * @var Repositories\AccountDetail
	 */
	private $repository;

	public function setUp() {
		$this->repository = new Repositories\AccountDetail();
	}

	public function testValidStripeAccountId(){
		$accountId = 'account_id';
		give_update_option(
			'_give_stripe_get_all_accounts',
			[
				$accountId => [
					'type' => 'manual',
					'account_name' => 'Account 1',
					'account_slug' => $accountId,
					'account_email' => '',
					'account_country' => '',
					'account_id' => '',
					'live_secret_key' => 'dummy',
					'test_secret_key' => 'dummy',
					'live_publishable_key' => 'dummy',
					'test_publishable_key' => 'dummy',
				]
			]
		);

		$account = $this->repository->getAccountDetail( $accountId );
		$this->assertSame( $accountId, $account->accountId );
	}

	public function testNotValidStripeAccountId(){
		$accountId = 'account_id';
		give_update_option(
			'_give_stripe_get_all_accounts',
			[
				$accountId => [
					'type' => 'manual',
					'account_name' => 'Account 1',
					'account_slug' => $accountId,
					'account_email' => '',
					'account_country' => '',
					'account_id' => '',
					'live_secret_key' => 'dummy',
					'test_secret_key' => 'dummy',
					'live_publishable_key' => 'dummy',
					'test_publishable_key' => 'dummy',
				]
			]
		);


		$this->expectException( InvalidArgumentException::class );
		$this->repository->getAccountDetail( 'account_2' );
	}
}

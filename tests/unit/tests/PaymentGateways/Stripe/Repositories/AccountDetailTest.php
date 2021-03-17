<?php

use PHPUnit\Framework\TestCase;
use Give\PaymentGateways\Stripe\Repositories;

class AccountDetailTest extends TestCase{
	/**
	 * @var Repositories\AccountDetail
	 */
	private $repository;

	/**
	 * @var Give_Donate_Form
	 */
	private $form;

	public function setUp() {
		$this->repository = new Repositories\AccountDetail();
		$this->form = Give_Helper_Form::create_simple_form();
	}

	public function testDonationFormUseGlobalDefaultStripeAccount(){
		$globalStripeAccountId = 'account_1';

		give_update_option(
			'_give_stripe_get_all_accounts',
			[
				$globalStripeAccountId => [
					'type' => 'manual',
					'account_name' => 'Account 1',
					'account_slug' => $globalStripeAccountId,
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
		give_update_option( '_give_stripe_default_account', $globalStripeAccountId );
		give_get_meta( $this->form->get_ID(), 'give_stripe_per_form_accounts', false );

		$this->assertSame( $globalStripeAccountId, $this->repository->getDonationFormStripeAccountId( $this->form->get_ID() ) );
	}

	public function testDonationFormUseManuallySelectedStripeAccount(){
		$globalStripeAccountId = 'account_1';
		$manuallySelectedStripeAccountId = 'account_2';

		give_update_option(
			'_give_stripe_get_all_accounts',
			[
				$manuallySelectedStripeAccountId => [
					'type' => 'manual',
					'account_name' => 'Account 1',
					'account_slug' => $manuallySelectedStripeAccountId,
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
		give_update_option( '_give_stripe_default_account', $globalStripeAccountId );
		give_get_meta( $this->form->get_ID(), '_give_stripe_default_account', true );
		give_get_meta( $this->form->get_ID(), 'give_stripe_per_form_accounts', $manuallySelectedStripeAccountId );

		$this->assertSame( $manuallySelectedStripeAccountId, $this->repository->getDonationFormStripeAccountId( $this->form->get_ID() ) );
	}

	public function testValidStripeAccountId(){
		$accountId = 'account_1';
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
		$accountId = 'account_1';
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

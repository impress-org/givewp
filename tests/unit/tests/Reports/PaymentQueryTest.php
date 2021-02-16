<?php declare( strict_types=1 );

use PHPUnit\Framework\TestCase;
use Give\Reports\PaymentsQuery;

final class PaymentQueryTest extends TestCase {

	public function setUp(): void {
		// Mock $wpdb
		$this->wpdb         = new stdClass;
		$this->wpdb->prefix = 'test_';
	}

	/*
	|--------------------------------------------------------------------------
	| Test Mode
	|--------------------------------------------------------------------------
	|
	*/

	public function testDonationTestModeDefault() {
		$query = new PaymentsQuery;
		$this->assertContains( "AND DonationMode.meta_value = 'live'", $query->getSQL( $this->wpdb ) );
	}

	public function testDonationTestMode() {
		$query = ( new PaymentsQuery )->testMode();
		$this->assertContains( "AND DonationMode.meta_value = 'test'", $query->getSQL( $this->wpdb ) );
	}

	public function testDonationTestModeRevert() {
		$query = ( new PaymentsQuery )->testMode( false );
		$this->assertContains( "AND DonationMode.meta_value = 'live'", $query->getSQL( $this->wpdb ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Currency
	|--------------------------------------------------------------------------
	|
	*/

	public function testDonationCurrencyDefault() {
		$query = new PaymentsQuery;
		$this->assertContains( "AND DonationCurrency.meta_value = 'USD'", $query->getSQL( $this->wpdb ) );
	}

	public function testDonationCurrency() {
		$query = ( new PaymentsQuery )->currency( 'EUR' );
		$this->assertContains( "AND DonationCurrency.meta_value = 'EUR'", $query->getSQL( $this->wpdb ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Donation date between
	|--------------------------------------------------------------------------
	|
	*/

	public function testDonationsBetweenDefault() {
		$query = new PaymentsQuery;
		$this->assertNotContains( "BETWEEN", $query->getSQL( $this->wpdb ) );
	}

	public function testDonationsBetween() {
		$query = ( new PaymentsQuery )->between( "2019-11-14", "2020-11-24 23:59:59" );
		$this->assertContains( " AND DATE( Donation.post_date ) BETWEEN '2019-11-14' AND '2020-11-24 23:59:59'",
			$query->getSQL( $this->wpdb ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Limit
	|--------------------------------------------------------------------------
	|
	*/

	public function testDonationLimitDefault() {
		$query = new PaymentsQuery;
		$this->assertNotContains( "LIMIT", $query->getSQL( $this->wpdb ) );
	}

	public function testDonationLimit() {
		$query = ( new PaymentsQuery )->limit( 10 );
		$this->assertContains( "LIMIT 10", $query->getSQL( $this->wpdb ) );
	}
}

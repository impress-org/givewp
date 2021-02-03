<?php

use Give\TestData\Factories\DonationFactory;
use Give\TestData\Repositories\DonationRepository;

/**
 * Give API Test Case
 *
 * Adds setup for the REST API.
 * 
 * @link https://torquemag.io/2017/01/testing-api-endpoints/
 *
 * @since 2.9.7
 */
class Give_API_Test_Case extends Give_Unit_Test_Case {

	use AdminUser;
	use DonationForm;
	use Donor;
    use RefreshDonations;
    use RestServer;

	/**
	 * Test REST Server
	 *
	 * @var WP_REST_Server
	 */
	protected $server;

    public function setUp() {
        parent::setUp();

		$this->setupServer();
		$this->setupDonor();
		$this->setupDonationForm();
		$this->setupAdminUser();
		$this->refreshDonations();
	}

	protected function setDonations( $donations ) {
		$repository = give()->make( DonationRepository::class );

		foreach( $donations as $key => $donation ) {
			$repository->insertDonation( wp_parse_args( $donation, [
				'payment_gateway' => 'stripe', // Hard-coding a payment gateway to prevent 'manual' being set as random.
			] ), [] );
		}
	}
	
	protected function makeRequest( $method, $endpoint, $params = [] ) {
		$request = new WP_REST_Request( $method, $endpoint );
		$request->set_header( 'X-WP-nonce', wp_create_nonce( 'wp_rest' ) );
		foreach( $params as $key => $value ) {
			$request->set_param( $key, $value );
		}
		return $request;
	}
}

<?php

/**
 * Class Tests_API
 */
class Tests_API extends Give_Unit_Test_Case {

	/**
	 * @var null
	 */
	protected $_rewrite = null;

	/**
	 * @var null
	 */
	protected $query = null;

	/**
	 * @var null
	 */
	protected $_post = null;

	/**
	 * @var Give_API
	 */
	protected $_api = null;

	/**
	 * @var null
	 */
	protected $_api_output = null;

	/**
	 * @var null
	 */
	protected $_api_output_donations = null;

	/**
	 * @var null
	 */
	protected $_user_id = null;

	/**
	 * @var null
	 */
	protected $_payment_id = null;

	/**
	 * Set it up.
	 */
	public function setUp() {
		parent::setUp();

		global $wp_rewrite, $wp_query;

		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules( false );

		$this->_api = new Give_API();

		$user_args      = array(
			'first_name' => 'Admin',
			'last_name'  => 'User',
			'role'       => 'administrator',
		);
		$this->_user_id = $this->factory->user->create( $user_args );
		$user           = new WP_User( $this->_user_id );
		$user->add_cap( 'view_give_reports' );

		$user                = wp_set_current_user( $this->_user_id );
		$this->_user_id      = $user->ID;
		$this->_api->user_id = $this->_user_id;

		$this->_api->add_endpoint( (array) $wp_rewrite );

		$this->_rewrite = $wp_rewrite;
		$this->_query   = $wp_query;

		// Create a Donation Form.
		$post_id = $this->factory->post->create(
			array(
				'post_title'  => 'Test Form',
				'post_type'   => 'give_forms',
				'post_status' => 'publish',
			)
		);

		$_multi_level_donations = array(
			array(
				'_give_id'     => array(
					'level_id' => '1',
				),
				'_give_amount' => '10.00',
				'_give_text'   => 'Basic Level',
			),
			array(
				'_give_id'     => array(
					'level_id' => '2',
				),
				'_give_amount' => '20.00',
				'_give_text'   => 'Intermediate Level',
			),
			array(
				'_give_id'     => array(
					'level_id' => '3',
				),
				'_give_amount' => '40.00',
				'_give_text'   => 'Advanced Level',
			),
		);

		$meta = array(
			'give_price'            => '0.00',
			'_give_price_option'    => 'multi',
			'_give_donation_levels' => array_values( $_multi_level_donations ),
			'_give_form_earnings'   => 120,
			'_give_form_sales'      => 59,
			'_give_goal_option'     => 'enabled',
			'_give_set_goal'        => 2000,
		);
		foreach ( $meta as $key => $value ) {
			give_update_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		$user = get_userdata( $this->_user_id );

		$user_info = array(
			'id'         => $user->ID,
			'email'      => 'testadmin@domain.com',
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name,
		);

		$prices = give_get_meta( $post_id, '_give_donation_levels', true );
		$total  = $prices[1]['_give_amount'];

		give_update_meta( $post_id, '_give_form_content', 'Post content 1' );

		// Add a payment.
		$donation_data = array(
			'price'           => number_format( (float) $total, 2 ),
			'give_form_title' => get_the_title( $post_id ),
			'give_form_id'    => $post_id,
			'date'            => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'status'          => 'pending',
			'gateway'         => 'manual',
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';

		$this->_payment_id = give_insert_payment( $donation_data );

		give_update_payment_status( $this->_payment_id, 'complete' );

		$this->_api_output           = $this->_api->get_forms();
		$this->_api_output_donations = $this->_api->get_recent_donations();

		global $wp_query;
		$wp_query->query_vars['format'] = 'override';

		// Prevents give_die() from running.
		add_action( 'give_api_output_override', array( $this, 'give_test_api_return_helper' ), 10, 2 );

	}

	/**
	 * Helper function to return API data from Give_API()->output().
	 *
	 * Prevents give_die() from killing unit tests.
	 *
	 * @param $data array The data returned.
	 * @param $api  Give_API
	 *
	 * @return void
	 */
	public function give_test_api_return_helper( $data, $api ) {

		// Prevent give_die() from stopping tests.
		if ( ! defined( 'GIVE_UNIT_TESTS' ) ) {
			define( 'GIVE_UNIT_TESTS', true );
		}

	}


	/**
	 * Tear it Down
	 */
	public function tearDown() {
		parent::tearDown();
		remove_action( 'give_api_output_override_xml', array( $this, 'override_api_xml_format' ) );
		Give_Helper_Payment::delete_payment( $this->_payment_id );
	}

	/**
	 * Test Endpoints
	 */
	public function test_endpoints() {
		$this->assertEquals( 'give-api', $this->_rewrite->endpoints[0][1] );
	}

	/**
	 * Test Query Vars
	 */
	public function test_query_vars() {
		global $wp_filter;

		$out = $this->_api->query_vars( array() );
		$this->assertEquals( 'token', $out[0] );
		$this->assertEquals( 'key', $out[1] );
		$this->assertEquals( 'query', $out[2] );
		$this->assertEquals( 'type', $out[3] );
		$this->assertEquals( 'form', $out[4] );
		$this->assertEquals( 'number', $out[5] );
		$this->assertEquals( 'date', $out[6] );
		$this->assertEquals( 'startdate', $out[7] );
		$this->assertEquals( 'enddate', $out[8] );
		$this->assertEquals( 'donor', $out[9] );
		$this->assertEquals( 'format', $out[10] );
	}

	/**
	 * Test Get Versions
	 */
	public function test_get_versions() {
		$this->assertInternalType( 'array', $this->_api->get_versions() );
		$this->assertArrayHasKey( 'v1', $this->_api->get_versions() );
	}

	/**
	 * Test Get Default Version
	 */
	public function test_get_default_version() {

		$this->assertEquals( 'v1', $this->_api->get_default_version() );

		// define( 'GIVE_API_VERSION', 'v2' );
		// $this->assertEquals( 'v2', $this->_api->get_default_version() );
	}

	/**
	 * Test Get Queried Version
	 */
	public function test_get_queried_version() {

		global $wp_query;

		$_POST['give_set_api_key'] = 1;
		Give()->api->generate_api_key( $this->_user_id );

		$wp_query->query_vars['key']   = get_user_meta( $this->_user_id, 'give_user_public_key', true );
		$wp_query->query_vars['token'] = hash( 'md5', get_user_meta( $this->_user_id, 'give_user_secret_key', true ) . get_user_meta( $this->_user_id, 'give_user_public_key', true ) );

		// Set the version number.
		$wp_query->query_vars['give-api'] = 'v1/donations';
		$this->_api->process_query();
		$this->assertEquals( 'v1', $this->_api->get_queried_version() );

		// There's no v2 of the API currently.
		// $this->_api->process_query();
		// $this->assertEquals( 'v2', $this->_api->get_queried_version() );
	}

	/**
	 * Test Get Forms
	 */
	public function test_get_forms() {
		$out = $this->_api_output;
		$this->assertArrayHasKey( 'id', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'title', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'create_date', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'link', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'content', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'thumbnail', $out['forms'][0]['info'] );

		$this->assertEquals( 'test-form', $out['forms'][0]['info']['slug'] );
		$this->assertEquals( 'Test Form', $out['forms'][0]['info']['title'] );
		$this->assertEquals( 'publish', $out['forms'][0]['info']['status'] );
		$this->assertEquals( 'Post content 1', $out['forms'][0]['info']['content'] );
		$this->assertEquals( 'http://example.org/?give_forms=test-form', $out['forms'][0]['info']['link'] );
		$this->assertEquals( '', $out['forms'][0]['info']['thumbnail'] );
	}

	/**
	 * Test Get Form Stats
	 */
	public function test_get_form_stats() {
		$out = $this->_api_output;

		$this->assertArrayHasKey( 'stats', $out['forms'][0] );
		$this->assertArrayHasKey( 'total', $out['forms'][0]['stats'] );
		$this->assertArrayHasKey( 'donations', $out['forms'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'earnings', $out['forms'][0]['stats']['total'] );
		$this->assertArrayHasKey( 'monthly_average', $out['forms'][0]['stats'] );
		$this->assertArrayHasKey( 'donations', $out['forms'][0]['stats']['monthly_average'] );
		$this->assertArrayHasKey( 'earnings', $out['forms'][0]['stats']['monthly_average'] );

		$this->assertEquals( '60', $out['forms'][0]['stats']['total']['donations'] );
		$this->assertEquals( '140', $out['forms'][0]['stats']['total']['earnings'] );
		$this->assertEquals( '60', $out['forms'][0]['stats']['monthly_average']['donations'] );
		$this->assertEquals( '140', $out['forms'][0]['stats']['monthly_average']['earnings'] );
	}

	/**
	 * Test Get Form Goal
	 */
	public function test_get_form_goal() {
		$out = $this->_api_output;

		$this->assertArrayHasKey( 'goal', $out['forms'][0] );
		$this->assertArrayHasKey( 'amount', $out['forms'][0]['goal'] );
		$this->assertArrayHasKey( 'percentage_completed', $out['forms'][0]['goal'] );

		$this->assertEquals( '2000', $out['forms'][0]['goal']['amount'] );
		$this->assertEquals( '7.0', $out['forms'][0]['goal']['percentage_completed'] );

	}

	/**
	 * Test Get Forms Pricing
	 */
	public function test_get_forms_pricing() {
		$out = $this->_api_output;

		$this->assertArrayHasKey( 'pricing', $out['forms'][0] );
		$this->assertArrayHasKey( 'basiclevel', $out['forms'][0]['pricing'] );
		$this->assertArrayHasKey( 'intermediatelevel', $out['forms'][0]['pricing'] );
		$this->assertArrayHasKey( 'advancedlevel', $out['forms'][0]['pricing'] );

		$this->assertEquals( '10', $out['forms'][0]['pricing']['basiclevel'] );
		$this->assertEquals( '20', $out['forms'][0]['pricing']['intermediatelevel'] );
		$this->assertEquals( '40', $out['forms'][0]['pricing']['advancedlevel'] );
	}

	/**
	 * Test Get Recent Donations
	 */
	public function test_get_recent_donations() {
		$out = $this->_api_output_donations;

		$this->assertArrayHasKey( 'donations', $out );
		$this->assertArrayHasKey( 'ID', $out['donations'][0] );
		$this->assertArrayHasKey( 'key', $out['donations'][0] );
		$this->assertArrayHasKey( 'total', $out['donations'][0] );
		$this->assertArrayHasKey( 'gateway', $out['donations'][0] );
		$this->assertArrayHasKey( 'email', $out['donations'][0] );
		$this->assertArrayHasKey( 'date', $out['donations'][0] );
		$this->assertArrayHasKey( 'form', $out['donations'][0] );
		$this->assertArrayHasKey( 'id', $out['donations'][0]['form'] );
		$this->assertArrayHasKey( 'name', $out['donations'][0]['form'] );
		$this->assertArrayHasKey( 'price', $out['donations'][0]['form'] );
		$this->assertArrayHasKey( 'price_name', $out['donations'][0]['form'] );

		$this->assertEquals( 20.00, $out['donations'][0]['total'] );
		$this->assertEquals( 'manual', $out['donations'][0]['gateway'] );
		$this->assertEquals( 'testadmin@domain.com', $out['donations'][0]['email'] );
		$this->assertEquals( 'Test Form', $out['donations'][0]['form']['name'] );
		$this->assertEquals( 20, $out['donations'][0]['form']['price'] );
		$this->assertEquals( 'Intermediate Level', $out['donations'][0]['form']['price_name'] );
	}

	/**
	 * Test Update Key
	 */
	public function test_generate_api_key() {

		$_POST['give_set_api_key'] = 1;

		Give()->api->generate_api_key( $this->_user_id );

		$user_public = $this->_api->get_user_public_key( $this->_user_id );
		$user_secret = $this->_api->get_user_secret_key( $this->_user_id );

		$this->assertNotEmpty( $user_public );
		$this->assertNotEmpty( $user_secret );

	}

	/**
	 * Test Get User
	 */
	public function test_get_user() {

		$_POST['give_set_api_key'] = 1;

		Give()->api->generate_api_key( $this->_user_id );

		$this->assertEquals( $this->_user_id, $this->_api->get_user( $this->_api->get_user_public_key( $this->_user_id ) ) );

	}

	/**
	 * Test Get Donors
	 */
	public function test_get_donors() {
		$out = $this->_api->get_donors();

		$this->assertArrayHasKey( 'donors', $out );
		$this->assertArrayHasKey( 'info', $out['donors'][0] );
		$this->assertArrayHasKey( 'username', $out['donors'][0]['info'] );
		$this->assertArrayHasKey( 'display_name', $out['donors'][0]['info'] );
		$this->assertArrayHasKey( 'first_name', $out['donors'][0]['info'] );
		$this->assertArrayHasKey( 'last_name', $out['donors'][0]['info'] );
		$this->assertArrayHasKey( 'email', $out['donors'][0]['info'] );
		$this->assertArrayHasKey( 'stats', $out['donors'][0] );
		$this->assertArrayHasKey( 'total_donations', $out['donors'][0]['stats'] );
		$this->assertArrayHasKey( 'total_spent', $out['donors'][0]['stats'] );

		$this->assertEquals( $this->_user_id, $out['donors'][0]['info']['user_id'] );
		$this->assertEquals( 'Admin', $out['donors'][0]['info']['first_name'] );
		$this->assertEquals( 'User', $out['donors'][0]['info']['last_name'] );
		$this->assertEquals( 'testadmin@domain.com', $out['donors'][0]['info']['email'] );
		$this->assertEquals( 1, $out['donors'][0]['stats']['total_donations'] );
		$this->assertEquals( 20.0, $out['donors'][0]['stats']['total_spent'] );
	}

	/**
	 * Test Missing Authorization
	 */
	public function test_missing_auth() {

		global $wp_query;

		$wp_query->query_vars['key']      = '';
		$wp_query->query_vars['token']    = '';
		$wp_query->query_vars['give-api'] = 'donations';
		$this->_api->process_query();
		$out = $this->_api->get_output();

		$this->assertArrayHasKey( 'error', $out );
		$this->assertEquals( 'You must specify both a token and API key.', $out['error'] );
	}

	/**
	 * Test Invalid Authorization.
	 */
	public function test_invalid_auth() {

		global $wp_query;

		$_POST['give_set_api_key'] = 1;
		Give()->api->generate_api_key( $this->_user_id );
		$wp_query->query_vars['key']   = get_user_meta( $this->_user_id, 'give_user_public_key', true );
		$wp_query->query_vars['token'] = 'bad-token-val';

		$wp_query->query_vars['give-api'] = 'donations';
		$this->_api->process_query();
		$out = $this->_api->get_output();

		$this->assertArrayHasKey( 'error', $out );
		$this->assertEquals( 'Your request could not be authenticated.', $out['error'] );
	}

	/**
	 * Test invalid key.
	 */
	public function test_invalid_key() {
		global $wp_query;

		$_POST['give_set_api_key'] = 1;
		Give()->api->generate_api_key( $this->_user_id );
		$wp_query->query_vars['key']   = 'bad-key-val';
		$wp_query->query_vars['token'] = hash( 'md5', get_user_meta( $this->_user_id, 'give_user_secret_key', true ) . get_user_meta( $this->_user_id, 'give_user_public_key', true ) );

		$wp_query->query_vars['give-api'] = 'donations';
		$this->_api->process_query();
		$out = $this->_api->get_output();

		$this->assertArrayHasKey( 'error', $out );
		$this->assertEquals( 'Invalid API key.', $out['error'] );

	}

	/**
	 * Test Process Query
	 */
	public function test_process_query() {
		global $wp_query;

		$_POST['give_set_api_key'] = 1;
		$this->_api->generate_api_key( $this->_user_id );

		$wp_query->query_vars['give-api'] = 'forms';
		$wp_query->query_vars['key']      = get_user_meta( $this->_user_id, 'give_user_public_key', true );
		$wp_query->query_vars['token']    = hash( 'md5', get_user_meta( $this->_user_id, 'give_user_secret_key', true ) . get_user_meta( $this->_user_id, 'give_user_public_key', true ) );

		$this->_api->process_query();

		$out = $this->_api->get_output();

		$this->assertArrayHasKey( 'info', $out['forms'][0] );
		$this->assertArrayHasKey( 'id', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['forms'][0]['info'] );
		$this->assertEquals( 'test-form', $out['forms'][0]['info']['slug'] );
		$this->assertArrayHasKey( 'title', $out['forms'][0]['info'] );
		$this->assertEquals( 'Test Form', $out['forms'][0]['info']['title'] );
		$this->assertArrayHasKey( 'create_date', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['forms'][0]['info'] );
		$this->assertEquals( 'publish', $out['forms'][0]['info']['status'] );
		$this->assertArrayHasKey( 'link', $out['forms'][0]['info'] );
		$this->assertEquals( 'http://example.org/?give_forms=test-form', $out['forms'][0]['info']['link'] );

		$this->assertArrayHasKey( 'content', $out['forms'][0]['info'] );
		$this->assertEquals( 'Post content 1', $out['forms'][0]['info']['content'] );
		$this->assertArrayHasKey( 'thumbnail', $out['forms'][0]['info'] );

		$this->assertArrayHasKey( 'stats', $out['forms'][0] );
		$this->assertArrayHasKey( 'total', $out['forms'][0]['stats'] );
		$this->assertArrayHasKey( 'donations', $out['forms'][0]['stats']['total'] );

		$this->assertEquals( 60, $out['forms'][0]['stats']['total']['donations'] );
		$this->assertArrayHasKey( 'earnings', $out['forms'][0]['stats']['total'] );
		$this->assertEquals( 140, $out['forms'][0]['stats']['total']['earnings'] );
		$this->assertArrayHasKey( 'monthly_average', $out['forms'][0]['stats'] );
		$this->assertArrayHasKey( 'donations', $out['forms'][0]['stats']['monthly_average'] );
		$this->assertEquals( 60, $out['forms'][0]['stats']['monthly_average']['donations'] );
		$this->assertArrayHasKey( 'earnings', $out['forms'][0]['stats']['monthly_average'] );
		$this->assertEquals( 140, $out['forms'][0]['stats']['monthly_average']['earnings'] );

	}

}


<?php

/**
 * @group give_api
 */
class Tests_API extends Give_Unit_Test_Case {
	protected $_rewrite = null;

	protected $query = null;

	protected $_post = null;

	protected $_api = null;

	protected $_api_output = null;

	protected $_api_output_sales = null;

	protected $_user_id = null;
	
	protected $_payment_id = null;

	public function setUp() {
		parent::setUp();

		global $wp_rewrite, $wp_query;
		$GLOBALS['wp_rewrite']->init();
		flush_rewrite_rules( false );

		$this->_api = new Give_API;

		$roles = new Give_Roles;
		$roles->add_roles();
		$roles->add_caps();

		$this->_api->add_endpoint( $wp_rewrite );

		$this->_rewrite = $wp_rewrite;
		$this->_query   = $wp_query;


		//Create a Donation Form
		$post_id = $this->factory->post->create( array(
			'post_title'  => 'Test Form',
			'post_type'   => 'give_forms',
			'post_status' => 'publish'
		) );

		$this->_user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $this->_user_id );

		$_multi_level_donations = array(
			array(
				'_give_id'     => array( 'level_id' => '1' ),
				'_give_amount' => '10.00',
				'_give_text'   => 'Basic Level'
			),
			array(
				'_give_id'     => array( 'level_id' => '2' ),
				'_give_amount' => '20.00',
				'_give_text'   => 'Intermediate Level'
			),
			array(
				'_give_id'     => array( 'level_id' => '3' ),
				'_give_amount' => '40.00',
				'_give_text'   => 'Advanced Level'
			),
		);

		$meta = array(
			'give_price'               => '0.00',
			'_give_price_option'       => 'multi',
			'_give_donation_levels'    => array_values( $_multi_level_donations ),
			'_give_form_earnings'      => 120,
			'_give_form_sales'         => 59,
		);
		foreach ( $meta as $key => $value ) {
			give_update_meta( $post_id, $key, $value );
		}

		$this->_post = get_post( $post_id );

		$user = get_userdata( 1 );

		$user_info = array(
			'id'         => $user->ID,
			'email'      => 'testadmin@domain.com',
			'first_name' => $user->first_name,
			'last_name'  => $user->last_name
		);

		$prices = give_get_meta( $post_id, '_give_donation_levels', true );
		$total  = $prices[1]['_give_amount'];

		//Add a payment
		$purchase_data = array(
			'price'           => number_format( (float) $total, 2 ),
			'give_form_title' => get_the_title( $post_id ),
			'give_form_id'    => $post_id,
			'date'            => date( 'Y-m-d H:i:s', strtotime( '-1 day' ) ),
			'purchase_key'    => strtolower( md5( uniqid() ) ),
			'user_email'      => $user_info['email'],
			'user_info'       => $user_info,
			'currency'        => 'USD',
			'status'          => 'pending',
			'gateway'         => 'manual'
		);

		$_SERVER['REMOTE_ADDR'] = '10.0.0.0';

		$this->_payment_id = give_insert_payment( $purchase_data );

		give_update_payment_status( $this->_payment_id, 'complete' );

		$this->_api_output       = $this->_api->get_forms();
		$this->_api_output_sales = $this->_api->get_recent_donations();

		global $wp_query;
		$wp_query->query_vars['format'] = 'override';
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

		foreach ( $wp_filter['query_vars'][10] as $arr ) :

			if ( 'query_vars' == $arr['function'][1] ) {
				$this->assertTrue( true );
			}

		endforeach;

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

		define( 'GIVE_API_VERSION', 'v2' );
		$this->assertEquals( 'v2', $this->_api->get_default_version() );

	}

	/**
	 * Test Get Queried Version
	 */
	public function test_get_queried_version() {
		$this->markTestIncomplete( 'This test is causing the suite to die for some reason.' );
		global $wp_query;

		$wp_query->query_vars['give-api'] = 'donations';

		$this->_api->process_query();

		$this->assertEquals( 'v1', $this->_api->get_queried_version() );

		define( 'GIVE_API_VERSION', 'v2' );

		$this->_api->process_query();

		$this->assertEquals( 'v2', $this->_api->get_queried_version() );

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
		$this->assertEquals( '', $out['forms'][0]['info']['content'] );
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
		$out = $this->_api_output_sales;

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
	public function test_update_key() {

		$_POST['give_set_api_key'] = 1;

		Give()->api->update_key( $this->_user_id );

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

		Give()->api->update_key( $this->_user_id );

		$this->assertEquals( $this->_user_id, $this->_api->get_user( $this->_api->get_user_public_key( $this->_user_id ) ) );

	}

	/**
	 * Test Get Donors
	 */
	public function test_get_donors() {
		$out = $this->_api->get_customers();

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

		$this->assertEquals( 1, $out['donors'][0]['info']['user_id'] );
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
		$this->markTestIncomplete( 'Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error).' );
		//$this->_api->missing_auth();
		//$out = $this->_api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'You must specify both a token and API key!', $out['error'] );

	}

	public function test_invalid_auth() {
		$this->markTestIncomplete( 'Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error).' );
		//$this->_api->invalid_auth();
		//$out = $this->_api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Your request could not be authenticated!', $out['error'] );
	}

	public function test_invalid_key() {
		$this->markTestIncomplete( 'Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error).' );
		//$out = $this->_api->invalid_key();
		//$out = $this->_api->get_output();
		//$this->assertArrayHasKey( 'error', $out );
		//$this->assertEquals( 'Invalid API key!', $out['error'] );
	}

	/**
	 * Test Process Query
	 */
	public function test_process_query() {
		global $wp_query;

		$this->markTestIncomplete( 'Needs to be rewritten since this outputs xml that kills travis with a 255 error (fatal PHP error).' );
		$_POST['give_set_api_key'] = 1;

		$this->_api->update_key( $this->_user_id );

		$wp_query->query_vars['give-api'] = 'forms';
		$wp_query->query_vars['key']      = get_user_meta( $this->_user_id, 'give_user_public_key', true );
		$wp_query->query_vars['token']    = hash( 'md5', get_user_meta( $this->_user_id, 'give_user_secret_key', true ) . get_user_meta( $this->_user_id, 'give_user_public_key', true ) );

		$this->_api->process_query();

		$out = $this->_api->get_output();

		$this->assertArrayHasKey( 'info', $out['forms'][0] );
		$this->assertArrayHasKey( 'id', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'slug', $out['forms'][0]['info'] );
		$this->assertEquals( 'test-download', $out['forms'][0]['info']['slug'] );
		$this->assertArrayHasKey( 'title', $out['forms'][0]['info'] );
		$this->assertEquals( 'Test Download', $out['forms'][0]['info']['title'] );
		$this->assertArrayHasKey( 'create_date', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'modified_date', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'status', $out['forms'][0]['info'] );
		$this->assertEquals( 'publish', $out['forms'][0]['info']['status'] );
		$this->assertArrayHasKey( 'link', $out['forms'][0]['info'] );
		$this->assertArrayHasKey( 'content', $out['forms'][0]['info'] );
		$this->assertEquals( 'Post content 1', $out['forms'][0]['info']['content'] );
		$this->assertArrayHasKey( 'thumbnail', $out['forms'][0]['info'] );

		$this->markTestIncomplete( 'This test needs to be fixed. The stats key doesn\'t exist due to not being able to correctly check the user\'s permissions.' );
		$this->assertArrayHasKey( 'stats', $out['forms'][0] );
		$this->assertArrayHasKey( 'total', $out['forms'][0]['stats'] );
		$this->assertArrayHasKey( 'donations', $out['forms'][0]['stats']['total'] );
		$this->assertEquals( 59, $out['forms'][0]['stats']['total']['donations'] );
		$this->assertArrayHasKey( 'earnings', $out['forms'][0]['stats']['total'] );
		$this->assertEquals( 129.43, $out['forms'][0]['stats']['total']['earnings'] );
		$this->assertArrayHasKey( 'monthly_average', $out['forms'][0]['stats'] );
		$this->assertArrayHasKey( 'donations', $out['forms'][0]['stats']['monthly_average'] );
		$this->assertEquals( 59, $out['forms'][0]['stats']['monthly_average']['donations'] );
		$this->assertArrayHasKey( 'earnings', $out['forms'][0]['stats']['monthly_average'] );
		$this->assertEquals( 129.43, $out['forms'][0]['stats']['monthly_average']['earnings'] );

		$this->assertArrayHasKey( 'pricing', $out['forms'][0] );
		$this->assertArrayHasKey( 'simple', $out['forms'][0]['pricing'] );
		$this->assertEquals( 20, $out['forms'][0]['pricing']['simple'] );
		$this->assertArrayHasKey( 'advanced', $out['forms'][0]['pricing'] );
		$this->assertEquals( 100, $out['forms'][0]['pricing']['advanced'] );

		$this->assertArrayHasKey( 'files', $out['forms'][0] );
		$this->assertArrayHasKey( 'name', $out['forms'][0]['files'][0] );
		$this->assertArrayHasKey( 'file', $out['forms'][0]['files'][0] );
		$this->assertArrayHasKey( 'condition', $out['forms'][0]['files'][0] );
		$this->assertArrayHasKey( 'name', $out['forms'][0]['files'][1] );
		$this->assertArrayHasKey( 'file', $out['forms'][0]['files'][1] );
		$this->assertArrayHasKey( 'condition', $out['forms'][0]['files'][1] );
		$this->assertEquals( 'File 1', $out['forms'][0]['files'][0]['name'] );
		$this->assertEquals( 'http://localhost/file1.jpg', $out['forms'][0]['files'][0]['file'] );
		$this->assertEquals( 0, $out['forms'][0]['files'][0]['condition'] );
		$this->assertEquals( 'File 2', $out['forms'][0]['files'][1]['name'] );
		$this->assertEquals( 'http://localhost/file2.jpg', $out['forms'][0]['files'][1]['file'] );
		$this->assertEquals( 'all', $out['forms'][0]['files'][1]['condition'] );

		$this->assertArrayHasKey( 'notes', $out['forms'][0] );
		$this->assertEquals( 'Donation Notes', $out['forms'][0]['notes'] );
	}

}


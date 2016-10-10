<?php


/**
 * @group give_logging
 */
class Tests_Logging extends Give_Unit_Test_Case {
	protected $_object = null;

	/**
	 * Set it Up
	 */
	public function setUp() {
		parent::setUp();

		$this->_object = new Give_Logging();
		$this->_object->register_post_type();
		$this->_object->register_taxonomy();
	}

	/**
	 * Tear it Down
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test Post Type
	 */
	public function test_post_type() {
		$wp_post_types = get_post_types( array(), 'names' );
		$this->assertArrayHasKey( 'give_log', $wp_post_types );
	}

	/**
	 * Test Logs CPT Labels
	 */
	public function test_post_type_labels() {
		$wp_post_types = get_post_types( array(), 'objects' );
		$this->assertEquals( 'Logs', $wp_post_types['give_log']->labels->name );
		$this->assertEquals( 'Logs', $wp_post_types['give_log']->labels->singular_name );
		$this->assertEquals( 'Add New', $wp_post_types['give_log']->labels->add_new );
		$this->assertEquals( 'Add New Post', $wp_post_types['give_log']->labels->add_new_item );
		$this->assertEquals( 'Edit Post', $wp_post_types['give_log']->labels->edit_item );
		$this->assertEquals( 'View Post', $wp_post_types['give_log']->labels->view_item );
		$this->assertEquals( 'Search Posts', $wp_post_types['give_log']->labels->search_items );
		$this->assertEquals( 'No posts found.', $wp_post_types['give_log']->labels->not_found );
		$this->assertEquals( 'No posts found in Trash.', $wp_post_types['give_log']->labels->not_found_in_trash );
		$this->assertEquals( 'Logs', $wp_post_types['give_log']->labels->all_items );
		$this->assertEquals( 'Logs', $wp_post_types['give_log']->labels->menu_name );
		$this->assertEquals( 'Logs', $wp_post_types['give_log']->labels->name_admin_bar );
		$this->assertEquals( '', $wp_post_types['give_log']->publicly_queryable );
		$this->assertEquals( 'post', $wp_post_types['give_log']->capability_type );
		$this->assertEquals( 1, $wp_post_types['give_log']->map_meta_cap );
		$this->assertEquals( '', $wp_post_types['give_log']->rewrite );
		$this->assertEquals( '', $wp_post_types['give_log']->has_archive );
		$this->assertEquals( 'Logs', $wp_post_types['give_log']->label );
	}

	/**
	 * Test Taxonomy Exist
	 */
	public function test_taxonomy_exist() {
		$wp_taxonomies = get_taxonomies( array(), 'names' );
		$this->assertArrayHasKey( 'give_log_type', $wp_taxonomies );
	}

	/**
	 * Test Log Types
	 */
	public function test_log_types() {
		$types = $this->_object->log_types();
		$this->assertEquals( 'sale', $types[0] );
		$this->assertEquals( 'gateway_error', $types[1] );
		$this->assertEquals( 'api_request', $types[2] );
	}

	/**
	 * Test Valid Log
	 */
	public function test_valid_log() {
		$this->assertTrue( $this->_object->valid_type( 'sale' ) );
	}

	/**
	 * Test Fake Log
	 */
	public function test_fake_log() {
		$this->assertFalse( $this->_object->valid_type( 'foo' ) );
	}

	/**
	 * Test Add
	 * 
	 * @covers Give_Logging::add
	 */
	public function test_add() {
		$this->assertNotNull( $this->_object->add() );
		$this->assertInternalType( 'integer', $this->_object->add() );
	}

	/**
	 * Test Insert Log
	 * 
	 * @covers Give_Logging::insert_log 
	 */
	public function test_insert_log() {
		$this->assertNotNull( $this->_object->insert_log( array( 'log_type' => 'sale' ) ) );
		$this->assertInternalType( 'integer', $this->_object->insert_log( array( 'log_type' => 'sale' ) ) );
	}

	/**
	 * Test Get Logs
	 *
	 * @covers Give_Logging::get_logs
	 */
	public function test_get_logs() {
		$args   = array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		);
		$log_id = $this->_object->insert_log( $args );
		$out    = $this->_object->get_logs( 1, 'sale' );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'post_author', $out[0] );
		$this->assertObjectHasAttribute( 'post_date', $out[0] );
		$this->assertObjectHasAttribute( 'post_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content', $out[0] );
		$this->assertObjectHasAttribute( 'post_title', $out[0] );
		$this->assertObjectHasAttribute( 'post_excerpt', $out[0] );
		$this->assertObjectHasAttribute( 'post_status', $out[0] );
		$this->assertObjectHasAttribute( 'comment_status', $out[0] );
		$this->assertObjectHasAttribute( 'ping_status', $out[0] );
		$this->assertObjectHasAttribute( 'post_password', $out[0] );
		$this->assertObjectHasAttribute( 'post_name', $out[0] );
		$this->assertObjectHasAttribute( 'to_ping', $out[0] );
		$this->assertObjectHasAttribute( 'pinged', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content_filtered', $out[0] );
		$this->assertObjectHasAttribute( 'post_parent', $out[0] );
		$this->assertObjectHasAttribute( 'guid', $out[0] );
		$this->assertObjectHasAttribute( 'menu_order', $out[0] );
		$this->assertObjectHasAttribute( 'post_type', $out[0] );
		$this->assertObjectHasAttribute( 'post_mime_type', $out[0] );
		$this->assertObjectHasAttribute( 'comment_count', $out[0] );
		$this->assertObjectHasAttribute( 'filter', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->post_content );
		$this->assertEquals( 'Test Log', $out[0]->post_title );
		$this->assertEquals( 'give_log', $out[0]->post_type );
	}

	/**
	 * Test Get Connected Logs
	 *
	 * @covers Give_Logging::get_connected_logs
	 */
	public function test_get_connected_logs() {
		$log_id = $this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$out    = $this->_object->get_connected_logs( array( 'post_parent' => 1, 'log_type' => 'sale' ) );

		$this->assertObjectHasAttribute( 'ID', $out[0] );
		$this->assertObjectHasAttribute( 'post_author', $out[0] );
		$this->assertObjectHasAttribute( 'post_date', $out[0] );
		$this->assertObjectHasAttribute( 'post_date_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content', $out[0] );
		$this->assertObjectHasAttribute( 'post_title', $out[0] );
		$this->assertObjectHasAttribute( 'post_excerpt', $out[0] );
		$this->assertObjectHasAttribute( 'post_status', $out[0] );
		$this->assertObjectHasAttribute( 'comment_status', $out[0] );
		$this->assertObjectHasAttribute( 'ping_status', $out[0] );
		$this->assertObjectHasAttribute( 'post_password', $out[0] );
		$this->assertObjectHasAttribute( 'post_name', $out[0] );
		$this->assertObjectHasAttribute( 'to_ping', $out[0] );
		$this->assertObjectHasAttribute( 'pinged', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified', $out[0] );
		$this->assertObjectHasAttribute( 'post_modified_gmt', $out[0] );
		$this->assertObjectHasAttribute( 'post_content_filtered', $out[0] );
		$this->assertObjectHasAttribute( 'post_parent', $out[0] );
		$this->assertObjectHasAttribute( 'guid', $out[0] );
		$this->assertObjectHasAttribute( 'menu_order', $out[0] );
		$this->assertObjectHasAttribute( 'post_type', $out[0] );
		$this->assertObjectHasAttribute( 'post_mime_type', $out[0] );
		$this->assertObjectHasAttribute( 'comment_count', $out[0] );
		$this->assertObjectHasAttribute( 'filter', $out[0] );

		$this->assertEquals( 'This is a test log inserted from PHPUnit', $out[0]->post_content );
		$this->assertEquals( 'Test Log', $out[0]->post_title );
		$this->assertEquals( 'give_log', $out[0]->post_type );
	}

	/**
	 * Test Get Log Count
	 *
	 * @covers Give_Logging::get_log_count
	 */
	public function test_get_log_count() {
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );

		$this->assertInternalType( 'integer', $this->_object->get_log_count( 1, 'sale' ) );
		$this->assertEquals( 5, $this->_object->get_log_count( 1, 'sale' ) );
	}

	/**
	 * Test Delete Logs
	 *
	 * @covers Give_Logging::delete_logs
	 */
	public function test_delete_logs() {
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );
		$this->_object->insert_log( array(
			'log_type'     => 'sale',
			'post_parent'  => 1,
			'post_title'   => 'Test Log',
			'post_content' => 'This is a test log inserted from PHPUnit'
		) );

		$this->assertNull( $this->_object->delete_logs( 1 ) );
	}
}
<?php

/**
 * Class Tests_Post_Types
 */
class Tests_Post_Types extends Give_Unit_Test_Case {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * @covers ::give_setup_post_types
	 */
	public function test_give_post_type() {
		$wp_post_types = get_post_types( array(), 'names' );
		$this->assertArrayHasKey( 'give_forms', $wp_post_types );
	}

	/**
	 * Test Post Type Labels
	 */
	public function test_give_post_type_labels() {
		$wp_post_types = get_post_types( array(), 'objects' );
		$this->assertEquals( 'Donation Forms', $wp_post_types['give_forms']->labels->name );
		$this->assertEquals( 'Form', $wp_post_types['give_forms']->labels->singular_name );
		$this->assertEquals( 'Add Form', $wp_post_types['give_forms']->labels->add_new );
		$this->assertEquals( 'Add New Donation Form', $wp_post_types['give_forms']->labels->add_new_item );
		$this->assertEquals( 'Edit Donation Form', $wp_post_types['give_forms']->labels->edit_item );
		$this->assertEquals( 'New Form', $wp_post_types['give_forms']->labels->new_item );
		$this->assertEquals( 'All Forms', $wp_post_types['give_forms']->labels->all_items );
		$this->assertEquals( 'View Form', $wp_post_types['give_forms']->labels->view_item );
		$this->assertEquals( 'Search Forms', $wp_post_types['give_forms']->labels->search_items );
		$this->assertEquals( 'No forms found.', $wp_post_types['give_forms']->labels->not_found );
		$this->assertEquals( 'No forms found in Trash.', $wp_post_types['give_forms']->labels->not_found_in_trash );
		$this->assertEquals( 'Donations', $wp_post_types['give_forms']->labels->menu_name );
		$this->assertEquals( 'Donation Form', $wp_post_types['give_forms']->labels->name_admin_bar );
		$this->assertEquals( 1, $wp_post_types['give_forms']->publicly_queryable );
		$this->assertEquals( 'give_form', $wp_post_types['give_forms']->capability_type );
		$this->assertEquals( 1, $wp_post_types['give_forms']->map_meta_cap );
		$this->assertEquals( 'donations', $wp_post_types['give_forms']->rewrite['slug'] );
		$this->assertEquals( 1, $wp_post_types['give_forms']->has_archive );
		$this->assertEquals( 'give_forms', $wp_post_types['give_forms']->query_var );
		$this->assertEquals( 'Donation Forms', $wp_post_types['give_forms']->label );
	}

	/**
	 * Test Donation CPT Exists
	 */
	public function test_payment_post_type() {
		$wp_post_types = get_post_types( array(), 'names' );
		$this->assertArrayHasKey( 'give_payment', $wp_post_types );
	}

	/**
	 * Test Donation CPT Labels
	 */
	public function test_payment_post_type_labels() {
		$wp_post_types = get_post_types( array(), 'objects' );
		$this->assertEquals( 'Donations', $wp_post_types['give_payment']->labels->name );
		$this->assertEquals( 'Donation', $wp_post_types['give_payment']->labels->singular_name );
		$this->assertEquals( 'Add New', $wp_post_types['give_payment']->labels->add_new );
		$this->assertEquals( 'Add New Donation', $wp_post_types['give_payment']->labels->add_new_item );
		$this->assertEquals( 'Edit Donation', $wp_post_types['give_payment']->labels->edit_item );
		$this->assertEquals( 'New Donation', $wp_post_types['give_payment']->labels->new_item );
		$this->assertEquals( 'All Donations', $wp_post_types['give_payment']->labels->all_items );
		$this->assertEquals( 'View Donation', $wp_post_types['give_payment']->labels->view_item );
		$this->assertEquals( 'Search Donations', $wp_post_types['give_payment']->labels->search_items );
		$this->assertEquals( 'No donations found.', $wp_post_types['give_payment']->labels->not_found );
		$this->assertEquals( 'No donations found in Trash.', $wp_post_types['give_payment']->labels->not_found_in_trash );
		$this->assertEquals( 'Donations', $wp_post_types['give_payment']->labels->menu_name );
		$this->assertEquals( 'Donation', $wp_post_types['give_payment']->labels->name_admin_bar );
		$this->assertEquals( '', $wp_post_types['give_payment']->publicly_queryable );
		$this->assertEquals( 'give_payment', $wp_post_types['give_payment']->capability_type );
		$this->assertEquals( 1, $wp_post_types['give_payment']->exclude_from_search );
		$this->assertEquals( 1, $wp_post_types['give_payment']->map_meta_cap );
		$this->assertEquals( 'Donations', $wp_post_types['give_payment']->label );
	}

	/**
	 * Test Registering Post Statuses
	 */
	public function test_register_post_statuses() {
		give_register_post_type_statuses();

		$wp_post_statuses = get_post_stati( array(), 'objects' );

		$this->assertInternalType( 'object', $wp_post_statuses['refunded'] );
		$this->assertInternalType( 'object', $wp_post_statuses['failed'] );
		$this->assertInternalType( 'object', $wp_post_statuses['revoked'] );
		$this->assertInternalType( 'object', $wp_post_statuses['cancelled'] );
		$this->assertInternalType( 'object', $wp_post_statuses['abandoned'] );
		$this->assertInternalType( 'object', $wp_post_statuses['processing'] );
	}
}

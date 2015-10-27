<?php

/**
 * @group give_cpt
 */
class Tests_Post_Types extends WP_UnitTestCase {
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
		global $wp_post_types;
		$this->assertArrayHasKey( 'give_forms', $wp_post_types );
	}

	public function test_give_post_type_labels() {
		global $wp_post_types;
		$this->assertEquals( 'Donation Forms', $wp_post_types['give_forms']->labels->name );
		$this->assertEquals( 'Form', $wp_post_types['give_forms']->labels->singular_name );
		$this->assertEquals( 'Add Form', $wp_post_types['give_forms']->labels->add_new );
		$this->assertEquals( 'Add New Donation Form', $wp_post_types['give_forms']->labels->add_new_item );
		$this->assertEquals( 'Edit Donation Form', $wp_post_types['give_forms']->labels->edit_item );
		$this->assertEquals( 'View Form', $wp_post_types['give_forms']->labels->view_item );
		$this->assertEquals( 'Search Forms', $wp_post_types['give_forms']->labels->search_items );
		$this->assertEquals( 'No Forms found', $wp_post_types['give_forms']->labels->not_found );
		$this->assertEquals( 'No Forms found in Trash', $wp_post_types['give_forms']->labels->not_found_in_trash );
		$this->assertEquals( 'All Forms', $wp_post_types['give_forms']->labels->all_items );
		$this->assertEquals( 'Donations', $wp_post_types['give_forms']->labels->menu_name );
		$this->assertEquals( 'Donation Form', $wp_post_types['give_forms']->labels->name_admin_bar );
		$this->assertEquals( 1, $wp_post_types['give_forms']->publicly_queryable );
		$this->assertEquals( 'give_forms', $wp_post_types['give_forms']->capability_type );
		$this->assertEquals( 1, $wp_post_types['give_forms']->map_meta_cap );
		$this->assertEquals( 'donations', $wp_post_types['give_forms']->rewrite['slug'] );
		$this->assertEquals( 1, $wp_post_types['give_forms']->has_archive );
		$this->assertEquals( 'give_forms', $wp_post_types['give_forms']->query_var );
		$this->assertEquals( 'Forms', $wp_post_types['give_forms']->label );
	}

	public function test_payment_post_type() {
		global $wp_post_types;
		$this->assertArrayHasKey( 'give_payment', $wp_post_types );
	}

	public function test_payment_post_type_labels() {
		global $wp_post_types;
		$this->assertEquals( 'Donations', $wp_post_types['give_payment']->labels->name );
		$this->assertEquals( 'Donation', $wp_post_types['give_payment']->labels->singular_name );
		$this->assertEquals( 'Add New', $wp_post_types['give_payment']->labels->add_new );
		$this->assertEquals( 'Add New Donation', $wp_post_types['give_payment']->labels->add_new_item );
		$this->assertEquals( 'Edit Donation', $wp_post_types['give_payment']->labels->edit_item );
		$this->assertEquals( 'View Donation', $wp_post_types['give_payment']->labels->view_item );
		$this->assertEquals( 'Search Donations', $wp_post_types['give_payment']->labels->search_items );
		$this->assertEquals( 'No Donations found', $wp_post_types['give_payment']->labels->not_found );
		$this->assertEquals( 'No Donations found in Trash', $wp_post_types['give_payment']->labels->not_found_in_trash );
		$this->assertEquals( 'All Donations', $wp_post_types['give_payment']->labels->all_items );
		$this->assertEquals( 'Transactions', $wp_post_types['give_payment']->labels->menu_name );
		$this->assertEquals( 'Donation', $wp_post_types['give_payment']->labels->name_admin_bar );
		$this->assertEquals( '', $wp_post_types['give_payment']->publicly_queryable );
		$this->assertEquals( 'give_payment', $wp_post_types['give_payment']->capability_type );
		$this->assertEquals( 1, $wp_post_types['give_payment']->exclude_from_search );
		$this->assertEquals( 1, $wp_post_types['give_payment']->map_meta_cap );
		$this->assertEquals( 'Donations', $wp_post_types['give_payment']->label );
	}

	public function test_register_post_statuses() {
		give_register_post_type_statuses();

		global $wp_post_statuses;

		$this->assertInternalType( 'object', $wp_post_statuses['refunded'] );
		$this->assertInternalType( 'object', $wp_post_statuses['revoked'] );
		$this->assertInternalType( 'object', $wp_post_statuses['failed'] );
		$this->assertInternalType( 'object', $wp_post_statuses['abandoned'] );
	}
}

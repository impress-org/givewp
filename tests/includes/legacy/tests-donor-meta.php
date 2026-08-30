<?php

/**
 * Class Tests_Donor_Meta
 */
class Tests_Donor_Meta extends Give_Unit_Test_Case {

	/**
	 * @var Give_Donor
	 */
	protected $_donor;

	/**
	 * @var int
	 */
	protected $_donor_id = 0;

	/**
	 * Set it up.
	 */
	public function setUp(): void {
		parent::setUp();

		$args = array(
			'email' => 'donor@test.com',
		);

		$this->_donor_id = Give()->donors->add( $args );

		$this->_donor = new Give_Donor( $this->_donor_id );

	}

	/**
	 * Test add metadata.
	 */
	function test_add_metadata() {
		$this->assertFalse( $this->_donor->add_meta( '', '' ) );
		$this->assertNotEmpty( $this->_donor->add_meta( 'test_key', '' ) );
		$this->assertNotEmpty( $this->_donor->add_meta( 'test_key', '1' ) );
	}

	/**
	 * Ensure direct metadata methods clear stale filter callback state.
	 *
	 * @since TBD
	 */
	function test_direct_metadata_methods_clear_stale_filter_callback_state() {
		$meta     = Give()->donor_meta;
		$property = new ReflectionProperty( Give_DB_Meta::class, 'is_filter_callback' );
		$property->setAccessible( true );

		$property->setValue( $meta, true );
		$meta->add_meta( 0, 'test_key', '1' );
		$this->assertFalse( $property->getValue( $meta ) );

		$property->setValue( $meta, true );
		$meta->update_meta( 0, 'test_key', '2' );
		$this->assertFalse( $property->getValue( $meta ) );

		$property->setValue( $meta, true );
		$meta->get_meta( 0, 'test_key', true );
		$this->assertFalse( $property->getValue( $meta ) );

		$property->setValue( $meta, true );
		$meta->delete_meta( 0, 'test_key' );
		$this->assertFalse( $property->getValue( $meta ) );
	}

	/**
	 * Ensure metadata filter bailout paths clear callback state.
	 *
	 * @since TBD
	 */
	function test_filter_bailout_clears_callback_state() {
		$meta     = Give()->donor_meta;
		$property = new ReflectionProperty( Give_DB_Meta::class, 'is_filter_callback' );
		$property->setAccessible( true );

		$meta->__add_meta( false, 0, 'test_key', '1', false );
		$this->assertFalse( $property->getValue( $meta ) );

		$meta->__get_meta( false, 0, 'test_key', true );
		$this->assertFalse( $property->getValue( $meta ) );

		$meta->__update_meta( null, 0, 'test_key', '2', '' );
		$this->assertFalse( $property->getValue( $meta ) );

		$meta->__delete_meta( false, 0, 'test_key', '', '' );
		$this->assertFalse( $property->getValue( $meta ) );
	}

	/**
	 * Test update metadata.
	 */
	function test_update_metadata() {
		$this->assertEmpty( $this->_donor->update_meta( '', '' ) );
		$this->assertNotEmpty( $this->_donor->update_meta( 'test_key_2', '' ) );
		$this->assertNotEmpty( $this->_donor->update_meta( 'test_key_2', '1' ) );
	}

	/**
	 * Test get metadata.
	 */
	function test_get_metadata() {
		$this->assertEmpty( $this->_donor->get_meta() );
		$this->assertEmpty( $this->_donor->get_meta( 'key_that_does_not_exist', true ) );
		$this->_donor->update_meta( 'test_key_2', '1' );
		$this->assertEquals( '1', $this->_donor->get_meta( 'test_key_2', true ) );
        $this->assertIsArray( $this->_donor->get_meta( 'test_key_2', false  ));
	}

	/**
	 * Test delete metadata.
	 */
	function test_delete_metadata() {
		$this->_donor->update_meta( 'test_key', '1' );
		$this->assertTrue( $this->_donor->delete_meta( 'test_key' ) );
		$this->assertFalse( $this->_donor->delete_meta( 'key_that_does_not_exist' ) );
	}

}

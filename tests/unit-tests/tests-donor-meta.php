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
	function setUp() {
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
		$this->assertInternalType( 'array', $this->_donor->get_meta( 'test_key_2', false ) );
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

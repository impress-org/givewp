<?php

/**
 * Class Tests_Country_Functions
 */
class Tests_Country_Functions extends Give_Unit_Test_Case {

	/**
	 * Set it up.
	 */
	function setUp() {
		parent::setUp();
	}

	/**
	 * Tear it down.
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test getting country by the key name.
	 *
	 * @since 1.8.12
	 */
	function test_give_get_states() {

		$us_states = give_get_states( 'US' );

		// Check some states.
		$this->assertArrayHasKey( 'MA', $us_states );
		$this->assertEquals( 'Massachusetts', $us_states['MA'] );

		$this->assertArrayHasKey( 'CA', $us_states );
		$this->assertEquals( 'California', $us_states['CA'] );

		$this->assertArrayHasKey( 'NC', $us_states );
		$this->assertEquals( 'North Carolina', $us_states['NC'] );

		$india_states = give_get_states( 'IN' );

		// Check some states.
		$this->assertArrayHasKey( 'CT', $india_states );
		$this->assertEquals( 'Chhattisgarh', $india_states['CT'] );

		$this->assertArrayHasKey( 'TN', $india_states );
		$this->assertEquals( 'Tamil Nadu', $india_states['TN'] );

		$this->assertArrayHasKey( 'AN', $india_states );
		$this->assertEquals( 'Andaman and Nicobar Islands', $india_states['AN'] );

	}

	/**
	 * Test getting country by the key name.
	 *
	 * @since 1.8.12
	 */
	function test_give_get_country_name_by_key() {

		$this->assertEquals( 'United States', give_get_country_name_by_key( 'US' ) );
		$this->assertEquals( 'Canada', give_get_country_name_by_key( 'CA' ) );
		$this->assertEquals( 'Bosnia and Herzegovina', give_get_country_name_by_key( 'BA' ) );
		$this->assertEquals( 'Congo, Republic of', give_get_country_name_by_key( 'CG' ) );
		$this->assertEquals( 'Georgia', give_get_country_name_by_key( 'GE' ) );
		$this->assertEquals( 'Isle of Man', give_get_country_name_by_key( 'IM' ) );
	}

}

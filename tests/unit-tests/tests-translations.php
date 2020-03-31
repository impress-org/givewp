<?php

/**
 * @group give_translations
 */
class Tests_Translations extends Give_Unit_Test_Case {

	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 *
	 * @since 2.0
	 * @cover Give_Translations::add_text
	 * @cover Give_Translations::add_label
	 * @cover Give_Translations::add_tooltip
	 */
	public function tests_add_text() {
		/**
		 * Text without text
		 */
		$error = Give_Translations::add_text(
			array(
				'text'  => '',
				'id'    => 'first_name',
				'group' => 'donation_form',
			)
		);

		$this->assertTrue( $error instanceof WP_Error );
		$this->assertTrue( array_key_exists( 'EMPTY_TEXT', $error->errors ) );

		/**
		 * Text without id
		 */
		$error = Give_Translations::add_text(
			array(
				'text'  => 'First Name',
				'group' => 'donation_form',
			)
		);

		$this->assertTrue( $error instanceof WP_Error );
		$this->assertTrue( array_key_exists( 'EMPTY_ID', $error->errors ) );

		/**
		 * Text in group
		 */
		$error = Give_Translations::add_text(
			array(
				'text'  => 'First Name',
				'id'    => 'first_name',
				'group' => 'donation_form',
			)
		);

		$this->assertFalse( $error );

		// Duplicate text in group
		$error = Give_Translations::add_text(
			array(
				'text'  => 'First Name',
				'id'    => 'first_name',
				'group' => 'donation_form',
			)
		);

		$this->assertEquals(
			'First Name',
			Give_Translations::get_text(
				array(
					'id'    => 'first_name',
					'group' => 'donation_form',
				)
			)
		);

		$this->assertTrue( $error instanceof WP_Error );
		$this->assertTrue( array_key_exists( 'TEXT_ID_WITHIN_GROUP_ALREADY_EXIST', $error->errors ) );

		/**
		 * Text without group
		 */
		$error = Give_Translations::add_text(
			array(
				'text' => 'First Name',
				'id'   => 'first_name',
			)
		);

		$this->assertFalse( $error );

		$error = Give_Translations::add_text(
			array(
				'text' => 'First Name',
				'id'   => 'first_name',
			)
		);

		$this->assertEquals(
			'First Name',
			Give_Translations::get_text(
				array(
					'id' => 'first_name',
				)
			)
		);

		$this->assertTrue( $error instanceof WP_Error );
		$this->assertTrue( array_key_exists( 'TEXT_ID_ALREADY_EXIST', $error->errors ) );
	}

	/**
	 *
	 * @since 2.0
	 * @cover Give_Translations::get_text
	 * @cover Give_Translations::get_label
	 * @cover Give_Translations::get_tooltip
	 */
	public function tests_get_text() {
		/**
		 * Text without id
		 */
		$text = Give_Translations::get_text(
			array(
				'text'  => '',
				'group' => 'donation_form',
			)
		);

		$this->assertEquals( '', $text );

		/**
		 * Text with id
		 */
		$text = Give_Translations::get_text(
			array(
				'id' => 'first_name',
			)
		);

		$this->assertEquals( 'First Name', $text );

		// Add custom text.
		Give_Translations::add_translation(
			array(
				'text' => 'Custom First Name',
				'id'   => 'first_name',
			)
		);

		$text = Give_Translations::get_text(
			array(
				'id' => 'first_name',
			)
		);

		$this->assertEquals( 'Custom First Name', $text );

		/**
		 * Text in group
		 */
		$text = Give_Translations::get_text(
			array(
				'id'    => 'first_name',
				'group' => 'donation_form',
			)
		);

		$this->assertEquals( 'First Name', $text );

		// Add custom text.
		Give_Translations::add_translation(
			array(
				'text'  => 'Custom First Name',
				'id'    => 'first_name',
				'group' => 'donation_form',
			)
		);

		$text = Give_Translations::get_text(
			array(
				'id'    => 'first_name',
				'group' => 'donation_form',
			)
		);

		$this->assertEquals( 'Custom First Name', $text );
	}
}

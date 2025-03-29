<?php

/**
 * Class Tests_Templates
 */
class Tests_Templates extends Give_Unit_Test_Case {

	protected $_post;

	/**
	 * Set up.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->_post = Give_Helper_Form::create_multilevel_form();

	}



	/**
	 * Test get_donation_form()
	 */
	public function test_get_donation_form() {

		$this->go_to( '/' );

		$args = array(
			'id' => $this->_post->ID,
		);

		ob_start();

		give_get_donation_form( $args );

		$form = ob_get_clean();
		// Remove html form whitespace.
		$form = preg_replace( '/\s+/S', ' ', $form );

        $this->assertIsString( $form );
		$this->assertStringContainsString( '<form id="give-form-', $form );
		$this->assertStringContainsString( 'class="give-form', $form );
		$this->assertStringContainsString( 'method="post">', $form );
		$this->assertStringContainsString( 'data-currency_position="before"', $form );
		$this->assertStringContainsString( 'data-currency_code="USD"', $form );
		$this->assertStringContainsString( 'data-currency_symbol="&#36;"', $form );
		$this->assertStringContainsString( 'data-decimal_separator="."', $form );
		$this->assertStringContainsString( 'data-thousands_separator=","', $form );
		$this->assertStringContainsString( 'data-number_decimals="2"', $form );

		// Test Hidden fields.
		$this->assertStringContainsString( '<input type="hidden" name="give-form-id" value="' . $this->_post->ID . '"/>', $form );
		$this->assertStringContainsString( '<input type="hidden" name="give-form-title" value="' . get_the_title( $this->_post->ID ) . '"/>', $form );
		$this->assertStringContainsString( '<input type="hidden" name="give-form-url" value="' . htmlspecialchars( give_get_current_page_url() ) . '"/>', $form );
		$this->assertStringContainsString( '<input type="hidden" name="give-current-url" value="' . htmlspecialchars( give_get_current_page_url() ) . '"/>', $form );
		$this->assertStringNotContainsString( '<input type="hidden" name="give-form-minimum" value="' . give_format_amount( give_get_form_minimum_price( $this->_post->ID ) ) . '"/>', $form );
		$this->assertStringContainsString( '<input id="give-form-honeypot-' . $this->_post->ID . '" type="text" name="give-honeypot" class="give-honeypot give-hidden"/>', $form );

		// The donation form we created has variable pricing, so ensure the price options render
		$this->assertStringContainsString( 'class="give-donation-levels-wrap', $form );
		$this->assertStringContainsString( '<input type="hidden" name="give-price-id"', $form );

		// Test a single price point as well
	}

	/**
	 * Test test_donation_form_amount_range()
	 */
	public function test_donation_form_amount_range() {

		$this->go_to( '/' );

		$args = array(
			'id' => $this->_post->ID,
		);

		// Enable Custom amount.
		give_update_meta( $this->_post->ID, '_give_custom_amount', 'enabled' );
		give_update_meta( $this->_post->ID, '_give_custom_amount_range_minimum', 1.000000 );
		give_update_meta( $this->_post->ID, '_give_custom_amount_range_maximum', 10.000000 );

		ob_start();

		give_get_donation_form( $args );

		$form = ob_get_clean();
		// Remove html form whitespace.
		$form = preg_replace( '/\s+/S', ' ', $form );

		$this->assertStringContainsString( '<input type="hidden" name="give-form-minimum" value="' . give_format_amount( give_get_form_minimum_price( $this->_post->ID ) ) . '"/>', $form );
		$this->assertStringContainsString( '<input type="hidden" name="give-form-maximum" value="' . give_format_amount( give_get_form_maximum_price( $this->_post->ID ) ) . '"/>', $form );

		// Custom amount disabled.
		give_delete_meta( $this->_post->ID, '_give_custom_amount' );
	}

	/**
	 * Test locate_template.
	 */
	public function test_locate_template() {
		// Test that a file path is found
		$this->assertIsString( give_locate_template( 'history-donations.php' ) );
	}

	/**
	 * Test get_theme_template_paths
	 */
	public function test_get_theme_template_paths() {
		$paths = give_get_theme_template_paths();
        $this->assertIsArray( $paths );
		$this->assertarrayHasKey( 1, $paths );
		$this->assertarrayHasKey( 10, $paths );
		$this->assertarrayHasKey( 100, $paths );
		$this->assertIsString( $paths[1] );
		$this->assertIsString( $paths[10] );
		$this->assertIsString( $paths[100] );
	}

	/**
	 * Test get_templates_dir_name
	 */
	public function test_get_templates_dir_name() {
		$this->assertEquals( 'give/', give_get_theme_template_dir_name() );
	}
}

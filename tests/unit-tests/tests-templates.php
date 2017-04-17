<?php

/**
 * Class Tests_Templates
 */
class Tests_Templates extends Give_Unit_Test_Case {

	protected $_post;

	/**
	 * Set up.
	 */
	public function setUp() {
		parent::setUp();
		$this->_post = Give_Helper_Form::create_multilevel_form();

	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test get_donation_form()
	 */
	public function test_get_donation_form() {

		$this->go_to( '/' );

		$args = array(
			'id' => $this->_post->ID
		);

		ob_start();

		give_get_donation_form( $args );

		$form = ob_get_clean();
		// Remove html form whitespace.
		$form = preg_replace( '/\s+/S', ' ', $form );

		$this->assertInternalType( 'string', $form );
		$this->assertContains( '<form id="give-form-', $form );
		$this->assertContains( 'class="give-form', $form );
		$this->assertContains( 'method="post">', $form );


		//Test Hidden fields.
		$this->assertContains( '<input type="hidden" name="give-form-id" value="' . $this->_post->ID . '"/>', $form );
		$this->assertContains( '<input type="hidden" name="give-form-title" value="' . get_the_title( $this->_post->ID ) . '"/>', $form );
		$this->assertContains( '<input type="hidden" name="give-form-url" value="' . htmlspecialchars( give_get_current_page_url() ) . '"/>', $form );
		$this->assertContains( '<input type="hidden" name="give-current-url" value="' . htmlspecialchars( give_get_current_page_url() ) . '"/>', $form );
		$this->assertContains( '<input type="hidden" name="give-form-minimum" value="' . give_format_amount( give_get_form_minimum_price( $this->_post->ID ) ) . '"/>', $form );
		$this->assertContains( '<input id="give-form-honeypot-' . $this->_post->ID . '" type="text" name="give-honeypot" class="give-honeypot give-hidden"/>', $form );

		// The donation form we created has variable pricing, so ensure the price options render
		$this->assertContains( 'class="give-donation-levels-wrap', $form );
		$this->assertContains( '<input type="hidden" name="give-price-id"', $form );


		// Test a single price point as well


	}

	/**
	 * Test locate_template.
	 */
	public function test_locate_template() {
		// Test that a file path is found
		$this->assertInternalType( 'string', give_locate_template( 'history-donations.php' ) );
	}

	/**
	 * Test get_theme_template_paths
	 */
	public function test_get_theme_template_paths() {
		$paths = give_get_theme_template_paths();
		$this->assertInternalType( 'array', $paths );
		$this->assertarrayHasKey( 1, $paths );
		$this->assertarrayHasKey( 10, $paths );
		$this->assertarrayHasKey( 100, $paths );
		$this->assertInternalType( 'string', $paths[1] );
		$this->assertInternalType( 'string', $paths[10] );
		$this->assertInternalType( 'string', $paths[100] );
	}

	/**
	 * Test get_templates_dir_name
	 */
	public function test_get_templates_dir_name() {
		$this->assertEquals( 'give/', give_get_theme_template_dir_name() );
	}
}

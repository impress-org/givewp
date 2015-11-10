<?php


/**
 * @group give_mime
 */
class Tests_Templates extends WP_UnitTestCase {

	protected $_post;

	public function setUp() {
		parent::setUp();


		$this->_post = Give_Helper_Form::create_multilevel_form();

	}

	public function tearDown() {
		parent::tearDown();
	}

	public function test_get_donation_form() {


		$this->go_to( '/' );

		$args = array(
			'id' => $this->_post->ID
		);

		ob_start();

		$form = give_get_donation_form( $args );

		$form = ob_get_clean();

		$this->assertInternalType( 'string', $form );
		$this->assertContains( '<form id="give-form-', $form );
		$this->assertContains( 'class="give-form', $form );
		$this->assertContains( 'method="post">', $form );
		$this->assertContains( '<input type="hidden" name="give-form-id" value="' . $this->_post->ID . '" />', $form );
		$this->assertContains( '<input type="hidden" name="give-form-title" value="' . get_the_title( $this->_post->ID ) . '" />', $form );
		$this->assertContains( '<input type="hidden" name="give-form-url" value="' . get_permalink( $this->_post->ID ) . '" />', $form );

		// The donation form we created has variable pricing, so ensure the price options render
		$this->assertContains( 'class="give-donation-levels-wrap', $form );
		$this->assertContains( '<input type="hidden" name="give-price-id"', $form );


		// Test a single price point as well


	}

	public function test_locate_template() {
		// Test that a file path is found
		$this->assertInternalType( 'string', give_locate_template( 'history-donations.php' ) );
	}

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

	public function test_get_templates_dir_name() {
		$this->assertEquals( 'give/', give_get_theme_template_dir_name() );
	}
}

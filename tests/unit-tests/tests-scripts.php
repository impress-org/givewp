<?php
/**
 * @group scripts
 */
class Tests_Scripts extends Give_Unit_Test_Case {
	
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}
	
	/**
	 * Test if all the file hooks are working.
	 *
	 * @since 1.0
	 */
	public function test_file_hooks() {

		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', 'give_load_scripts' ) );
		$this->assertNotFalse( has_action( 'wp_enqueue_scripts', 'give_register_styles' ) );
		$this->assertNotFalse( has_action( 'admin_enqueue_scripts', 'give_load_admin_scripts' ) );
		$this->assertNotFalse( has_action( 'admin_head', 'give_admin_icon' ) );

	}

	/**
	 * Test that all the scripts are loaded properly.
	 */
	public function test_load_frontend_scripts() {

		// Prepare test
		$this->go_to( '/' );
		give_load_scripts();

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {

			$this->assertTrue( wp_script_is( 'give-cc-validator', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'give-float-labels', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'give-blockui', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'give-accounting', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'give-magnific', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'give-checkout-global', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'give-scripts', 'enqueued' ) );
			$this->assertTrue( wp_script_is( 'give-ajax', 'enqueued' ) );

		} else {
			$this->assertTrue( wp_script_is( 'give', 'enqueued' ) );
		}


	}

	/**
	 * Test that the give_register_styles() function will bail when the 'disable_css'
	 * option is set to true.
	 *
	 * @since 1.0
	 */
	public function test_register_styles_bail_option() {

		// Prepare test
		$origin_disable_css = give_get_option( 'disable_css', false );
		give_update_option( 'disable_css', true );

		// Assert
		$this->assertNull( give_register_styles() );

		// Reset to origin
		give_update_option( 'disable_css', $origin_disable_css );

	}

	/**
	 * Test that the give_register_styles() function will enqueue the styles.
	 *
	 * @since 1.0
	 */
	public function test_register_styles() {

		give_update_option( 'disable_css', false );
		give_register_styles();

		// Assert
		$this->assertTrue( wp_style_is( 'give-styles', 'enqueued' ) );

	}


	/**
	 * Test that the give_load_admin_scripts() function will bail when not a Give admin page.
	 *
	 * @since 1.0
	 */
	public function test_load_admin_scripts_bail() {

		// Prepare test
		$screen         = get_current_screen();
		$origin_screen  = $screen->id;
		$current_screen = 'dashboard';

		require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-pages.php';

		// Assert
		$this->assertNull( give_load_admin_scripts( 'dashboard' ) );

		// Reset to origin
		$current_screen = $origin_screen;

	}

	/**
	 * Test that the give_load_admin_scripts() function will enqueue the proper styles.
	 *
	 * @since 1.0
	 */
	public function test_load_admin_scripts() {

		require_once GIVE_PLUGIN_DIR . 'includes/admin/admin-pages.php';

		give_load_admin_scripts( 'index.php' );

		$this->assertTrue( wp_style_is( 'jquery-ui-css', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'give-admin', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'jquery-chosen', 'enqueued' ) );
		$this->assertTrue( wp_style_is( 'thickbox', 'enqueued' ) );

		$this->assertTrue( wp_script_is( 'jquery-chosen', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'give-admin-scripts', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'jquery-ui-datepicker', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'jquery-flot', 'enqueued' ) );
		$this->assertTrue( wp_script_is( 'thickbox', 'enqueued' ) );

		//Forms CPT Script
		if ( $this->go_to( get_admin_url('edit.php?post_type=give_forms') ) ) {
			$this->assertTrue( wp_script_is( 'give-admin-forms-scripts', 'enqueued' ) );
		}
		$this->go_to( '/' );

	}

	/**
	 * Test that the give_admin_downloads_icon() function will display the proper styles.
	 *
	 * @since 1.0
	 */
	public function test_admin_icon() {

		ob_start();
		give_admin_icon();
		$return = ob_get_clean();

		$this->assertContains( '#adminmenu div.wp-menu-image.dashicons-give:before', $return );

	}


}

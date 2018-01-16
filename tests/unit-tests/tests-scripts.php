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
	 * Test that all the scripts are loaded properly.
	 */
	public function test_load_frontend_scripts() {

		// Prepare test
		$this->go_to( '/' );
		Give()->scripts->init();
		global $wp_scripts;
		foreach( $wp_scripts->queue as $handle ) :
			echo '<pre>';
			var_dump($handle);
			echo '</pre>';
		endforeach;
		$this->assertTrue( wp_script_is( 'give', 'enqueued' ) );

	}

	/**
	 * Test that the give_register_styles() function will bail when the 'css'
	 * option is set to true.
	 *
	 * @since 1.0
	 */
	public function test_register_styles_bail_option() {

		// Prepare test
		$origin_disable_css = give_get_option( 'css', false );
		give_update_option( 'css', true );

		// Assert
		$this->assertNull( Give()->scripts->register_styles() );

		// Reset to origin
		give_update_option( 'css', $origin_disable_css );

	}

	/**
	 * Test that the give_register_styles() function will enqueue the styles.
	 *
	 * @since 1.0
	 */
	public function test_register_styles() {

		give_update_option( 'css', false );
		Give()->scripts->register_styles();

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
		$this->assertNull( Give()->scripts->admin_enqueue_scripts( 'dashboard' ) );

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
		if ( $this->go_to( get_admin_url( 'edit.php?post_type=give_forms' ) ) ) {
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

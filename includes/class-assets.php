<?php
/**
 * Defines the Assets class
 *
 * @package Give\Includes
 * @since   2.1.0
 */

namespace Give\Includes;

/**
 * Loads the plugin's assets.
 *
 * Registers and enqueues plugin styles and scripts. Asset versions are based
 * on the current plugin version.
 *
 * All script and style handles should be registered in this class even if they
 * are enqueued dynamically by other classes.
 *
 * @since 2.1.0
 */
class Assets {
	/**
	 * URL of the assets directory.
	 *
	 * @since  2.1.0
	 * @var    string
	 * @access private
	 */
	private $url;

	/**
	 * Assets version.
	 *
	 * @since  2.1.0
	 * @var    string
	 * @access private
	 */
	private $version;

	/**
	 * Suffix used when loading minified assets.
	 *
	 * @since  2.1.0
	 * @var    string
	 * @access private
	 */
	private $suffix;

	/**
	 * Instantiates the Assets class.
	 *
	 * @since 2.1.0
	 */
	public function __construct( ) {
		$this->suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '': '.min';
	}

	/**
	 * Registers assets via WordPress hooks.
	 *
	 * @since 2.1.0
	 */
	public function register() {
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );

		if ( is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		} else {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_scripts' ) );
		}
	}

	/**
	 * Registers all plugin styles.
	 *
	 * @since 2.1.0
	 */
	public function register_styles() {
		wp_register_style( 'give-main-styles', GIVE_PLUGIN_URL . 'assets/dist/css/admin' . $this->suffix . '.css', array(), GIVE_VERSION );
		wp_register_style( 'give-styles', GIVE_PLUGIN_URL . 'assets/dist/css/give' . $this->suffix . '.css', array(), GIVE_VERSION );
	}

	/**
	 * Registers all plugin scripts.
	 *
	 * @since 2.1.0
	 */
	public function register_scripts() {
		wp_register_script( 'give', GIVE_PLUGIN_URL . 'assets/dist/js/admin' . $this->suffix . '.js', array(), GIVE_VERSION, true );
	}

	/**
	 * Enqueues admin styles.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_admin_styles() {
		wp_enqueue_style( 'give-main-styles' );
	}

	/**
	 * Enqueues public styles.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_public_styles() {
		wp_enqueue_style( 'give-styles' );
	}

	/**
	 * Enqueues admin scripts.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_admin_scripts() {
		wp_enqueue_script( 'give-main-script' );
	}

	/**
	 * Enqueues public scripts.
	 *
	 * @since 2.1.0
	 */
	public function enqueue_public_scripts() {
	}
}

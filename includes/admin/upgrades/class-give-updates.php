<?php

/**
 * Created by PhpStorm.
 * User: ravinderkumar
 * Date: 14/07/17
 * Time: 3:27 PM
 */
class Give_Updates {
	/**
	 * Instance.
	 *
	 * @since
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Updates
	 *
	 * @since  1.8.12
	 * @access private
	 * @var array
	 */
	static private $updates = array();

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8.12
	 * @access private
	 *
	 * @param Give_Updates .
	 */
	private function __construct() {
	}

	/**
	 * Register updates
	 *
	 * @param $args
	 */
	public static function register( $args ) {
		$args_default = array(
			'id'      => '',
			'version' => '',
		);

		$args = wp_parse_args( $args, $args_default );

		// You can only register database upgrade.
		$args['type'] = 'database';

		// Bailout.
		if ( $args['id'] || $args['version'] ) {
			return;
		}

		self::$updates[ $args['type'] ][ $args['version'] ] = $args;
	}


	/**
	 * Get updates.
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 * @param string $type Tye of update.
	 *
	 * @return array
	 */
	public function get_updates( $type = '' ) {
		$updates = ! empty( self::$updates[ $type ] ) ? self::$updates[ $type ] : array();

		return $updates;
	}


	/**
	 * Get instance.
	 *
	 * @since
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 *
	 * Setup hook
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 */
	public function setup_hooks() {
		add_action( 'admin_init', array( $this, 'change_donations_label' ), 9999 );
		add_action( 'admin_menu', array( $this, 'register_menu' ), 9999 );
	}

	/**
	 * Rename `Donations` menu title if updates exists
	 *
	 * @since  1.8.12
	 * @access public
	 */
	function change_donations_label() {
		global $menu;
		global $submenu;

		// Bailout.
		if ( empty( $menu ) || ! $this->get_update_count() ) {
			return;
		}

		foreach ( $menu as $index => $menu_item ) {
			if ( 'edit.php?post_type=give_forms' !== $menu_item[2] ) {
				continue;
			}

			$menu[ $index ][0] = sprintf(
				__( 'Donations <span class="update-plugins count-%1$d"><span class="plugin-count">%1$d</span></span>', 'give' ),
				$this->get_update_count()
			);

			break;
		}
	}

	/**
	 * Register upgrades menu
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function register_menu() {
		// Bailout.
		if( ! $this->get_update_count() ) {
			return;
		}

		//Upgrades
		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__( 'Give Updates', 'give' ),
			sprintf(
				'%1$s <span class="update-plugins count-%2$d"><span class="plugin-count">%2$d</span></span>',
				__( 'Updates', 'give' ),
				$this->get_update_count()
			),
			'manage_give_settings',
			'give-updates',
			array( $this, 'render_page' )
		);
	}

	/**
	 * Get tottal updates count
	 *
	 * @since  1.8.12
	 * @access public
	 * @return int
	 */
	public function get_db_update_count() {
		// @todo calculate total update count
		return 0;
	}


	/**
	 * Render Give Updates page
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function render_page() {
		include_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/views/upgrades.php';
	}

	/**
	 * Get addon update count.
	 *
	 * @since  1.8.12
	 * @access public
	 * @return int
	 */
	public function get_plugin_update_count() {
		$addons         = give_get_plugins();
		$plugin_updates = get_plugin_updates();
		$update_counter = 0;

		foreach ( $addons as $key => $info ) {
			if ( 'active' != $info['Status'] || 'add-on' != $info['Type'] || empty( $plugin_updates[ $key ] ) ) {
				continue;
			}

			$update_counter ++;
		}

		return $update_counter;
	}

	/**
	 * Get total update count
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 * @return int
	 */
	public function get_update_count() {
		$db_update_count     = $this->get_db_update_count();
		$plugin_update_count = $this->get_plugin_update_count();

		return ( $db_update_count + $plugin_update_count );
	}
}

Give_Updates::get_instance()->setup_hooks();
<?php

/**
 * Class Give_Updates
 *
 * @since 1.8.12
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
	private $updates = array();

	/**
	 * Current update percentage number
	 *
	 * @since  1.8.12
	 * @access private
	 * @var array
	 */
	public $percentage = 0;

	/**
	 * Current update step number
	 *
	 * @since  1.8.12
	 * @access private
	 * @var array
	 */
	public $step = 1;

	/**
	 * Current update number
	 *
	 * @since  1.8.12
	 * @access private
	 * @var array
	 */
	public $update = 1;

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
	 * @since  1.8.12
	 * @access public
	 *
	 * @param array $args
	 */
	public function register( $args ) {
		$args_default = array(
			'id'       => '',
			'version'  => '',
			'callback' => '',
		);

		$args = wp_parse_args( $args, $args_default );

		// You can only register database upgrade.
		$args['type'] = 'database';

		// Bailout.
		if ( empty( $args['id'] ) || empty( $args['version'] ) || empty( $args['callback'] ) || ! is_callable( $args['callback'] ) ) {
			return;
		}

		$this->updates[ $args['type'] ][] = $args;
	}


	/**
	 * Get updates.
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 * @param string $update_type Tye of update.
	 * @param string $status      Tye of update.
	 *
	 * @return array
	 */
	public function get_updates( $update_type = '', $status = 'all' ) {
		// return all updates.
		if ( empty( $update_type ) ) {
			return $this->updates;
		}

		// Get specific update.
		$updates = ! empty( $this->updates[ $update_type ] ) ? $this->updates[ $update_type ] : array();

		// Bailout.
		if ( empty( $updates ) ) {
			return $updates;
		}

		switch ( $status ) {
			case 'new':
				// Remove already completed updates.
				$completed_updates = give_get_completed_upgrades();

				if ( ! empty( $completed_updates ) ) {
					foreach ( $updates as $index => $update ) {
						if ( in_array( $update['id'], $completed_updates ) ) {
							unset( $updates[ $index ] );
						}
					}
					$updates = array_values( $updates );
				}

				break;
		}

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
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 *
	 * Setup hook
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function setup() {
		/**
		 * Setup hooks.
		 */
		add_action( 'init', array( $this, '__register_upgrade' ), 9999 );
		add_action( 'admin_init', array( $this, '__change_donations_label' ), 9999 );
		add_action( 'admin_menu', array( $this, '__register_menu' ), 9999 );
		add_action( 'give_set_upgrade_completed', array( $this, '__flush_resume_updates' ), 9999 );
		add_action( 'wp_ajax_give_do_ajax_updates', array( $this, '__give_ajax_updates' ) );

		/**
		 * Load file
		 */
		require_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';
	}

	/**
	 * Register plugin add-on updates.
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __register_plugin_addon_updates() {
		$addons         = give_get_plugins();
		$plugin_updates = get_plugin_updates();

		foreach ( $addons as $key => $info ) {
			if ( 'active' != $info['Status'] || 'add-on' != $info['Type'] || empty( $plugin_updates[ $key ] ) ) {
				continue;
			}

			$this->updates['plugin'][] = array_merge( $info, (array) $plugin_updates[ $key ] );
		}
	}


	/**
	 * Fire custom action hook to register updates
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __register_upgrade() {
		if ( ! is_admin() ) {
			return;
		}

		/**
		 * Fire the hook
		 *
		 * @since 1.8.12
		 */
		do_action( 'give_register_updates', $this );
	}

	/**
	 * Rename `Donations` menu title if updates exists
	 *
	 * @since  1.8.12
	 * @access public
	 */
	function __change_donations_label() {
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
	 * Register updates menu
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __register_menu() {

		// Load plugin updates.
		$this->__register_plugin_addon_updates();

		// Bailout.
		if ( ! $this->get_update_count() ) {
			// Show complete update message if still on update setting page.
			if ( isset( $_GET['page'] ) && 'give-updates' === $_GET['page'] ) {
				// Upgrades
				add_submenu_page(
					'edit.php?post_type=give_forms',
					esc_html__( 'Give Updates Complete', 'give' ),
					__( 'Updates', 'give' ),
					'manage_give_settings',
					'give-updates',
					array( $this, 'render_complete_page' )
				);
			}

			return;
		}

		// Upgrades
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
	 * Get total updates count
	 *
	 * @since  1.8.12
	 * @access public
	 * @return int
	 */
	public function get_db_update_count() {
		return count( $this->get_updates( 'database', 'new' ) );
	}

	/**
	 * Render Give Updates Completed page
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function render_complete_page() {
		include_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/views/upgrades-complete.php';
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
		return count( $this->get_updates( 'plugin' ) );
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


	/**
	 * Delete resume updates
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __flush_resume_updates() {
		delete_option( 'give_doing_upgrade' );
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );

		// Reset counter.
		$this->step = $this->percentage = 0;
		++ $this->update;
	}

	/**
	 *  Process give updates.
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __give_ajax_updates() {
		// Check permission.
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			$this->send_ajax_response(
				array(
					'message' => esc_html__( 'You do not have permission to do Give upgrades.', 'give' ),
				),
				'error'
			);
		}

		// Set params.
		$this->step   = absint( $_POST['step'] );
		$this->update = absint( $_POST['update'] );

		// Bailout: step and update must be positive and greater then zero.
		if ( ! $this->step ) {
			$this->send_ajax_response(
				array(
					'message'    => __( 'Please reload this page  and try again', 'give' ),
					'heading'    => '',
					'percentage' => 0,
				),
				'error'
			);
		}

		// Get updates.
		$updates = $this->get_updates( 'database', 'new' );

		// Bailout if we do not have nay updates.
		if ( empty( $updates ) ) {
			$this->send_ajax_response(
				array(
					'message'    => __( 'The database is already up to date.', 'give' ),
					'heading'    => __( 'Updates Completed.', 'give' ),
					'percentage' => 0,
				),
				'success'
			);
		}


		// Process update.
		foreach ( $updates as $index => $update ) {
			// Check if update depend upon any other update.
			if ( ! $this->is_parent_updates_completed( $update ) ) {
				continue;
			}

			// Run update.
			if ( is_array( $update['callback'] ) ) {
				$update['callback'][0]->$update['callback'][1]();
			} else {
				$update['callback']();
			}

			// Check if current update completed or not.
			if ( give_has_upgrade_completed( $update['id'] ) ) {
				if ( 1 === count( $updates ) ) {
					$this->send_ajax_response(
						array(
							'message'    => __( 'Database updated successfully.', 'give' ),
							'heading'    => __( 'Updates Completed.', 'give' ),
							'percentage' => 0,
						),
						'success'
					);
				}
			}

			$doing_upgrade_args = array(
				'update_info' => $update,
				'step'        => ++ $this->step,
				'update'      => $this->update,
				'heading'     => sprintf( 'Update %s of {update_count}', $this->update ),
				'percentage'  => $this->percentage,
			);

			// Cache upgrade.
			update_option( 'give_doing_upgrade', $doing_upgrade_args );

			$this->send_ajax_response( $doing_upgrade_args );
		}// End foreach().
	}

	/**
	 * Send ajax response
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 * @param        $data
	 * @param string $type
	 */
	public function send_ajax_response( $data, $type = '' ) {
		$default = array(
			'message'    => '',
			'heading'    => '',
			'percentage' => 0,
			'step'       => 0,
			'update'     => 0,
		);

		// Set data.
		$data = wp_parse_args( $data, $default );

		switch ( $type ) {
			case 'success':
				wp_send_json_success( $data );
				break;

			case 'error':
				wp_send_json_error( $data );
				break;

			default:
				wp_send_json( array(
					'data' => $data,
				) );
				break;
		}
	}


	/**
	 * Resume updates
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 * @return bool|int
	 */
	public function resume_updates() {
		$status = false;

		if ( $update = get_option( 'give_doing_upgrade' ) ) {
			$status = ! empty( $update['step'] ) ? $update['step'] : $status;
		}

		return $status;
	}


	/**
	 * Set current update percentage.
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 * @param $total
	 * @param $current_total
	 */
	public function set_percentage( $total, $current_total ) {
		// Set percentage.
		$this->percentage = $total ? ( ( $current_total ) / $total ) * 100 : 0;

		// Verify percentage.
		$this->percentage = ( 100 < $this->percentage ) ? 100 : $this->percentage;
	}

	/**
	 * Check if parent update completed or not.
	 *
	 * @since  2.0
	 * @access private
	 *
	 * @param array $update
	 *
	 * @return bool
	 */
	private function is_parent_updates_completed( $update ) {
		// Bailout.
		if ( empty( $update['depend'] ) ) {
			return true;
		}

		$is_dependency_completed = true;

		// Change param to array.
		if ( is_string( $update['depend'] ) ) {
			$update['depend'] = array( $update['depend'] );
		}

		foreach ( $update['depend'] as $depend ) {
			if ( ! give_has_upgrade_completed( $depend ) ) {
				$is_dependency_completed = false;
				break;
			}
		}

		return $is_dependency_completed;
	}
}

Give_Updates::get_instance()->setup();

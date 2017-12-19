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
	 * Instance.
	 *
	 * @since
	 * @access static
	 * @var Give_Background_Updater
	 */
	static private $background_updater;

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
		if (
			empty( $args['id'] ) ||
			empty( $args['version'] ) ||
			empty( $args['callback'] ) ||
			! is_callable( $args['callback'] )
		) {
			return;
		}

		// Change depend param to array.
		if ( isset( $args['depend'] ) && is_string( $args['depend'] ) ) {
			$args['depend'] = array( $args['depend'] );
		}

		$this->updates[ $args['type'] ][] = $args;
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
		 * Load file
		 */
		require_once GIVE_PLUGIN_DIR . 'includes/class-give-background-updater.php';
		require_once GIVE_PLUGIN_DIR . 'includes/admin/upgrades/upgrade-functions.php';

		self::$background_updater = new Give_Background_Updater();

		/**
		 * Setup hooks.
		 */
		add_action( 'init', array( $this, '__register_upgrade' ), 9999 );
		add_action( 'give_set_upgrade_completed', array( $this, '__flush_resume_updates' ), 9999 );
		add_action( 'wp_ajax_give_db_updates_info', array( $this, '__give_db_updates_info' ) );
		add_action( 'wp_ajax_give_run_db_updates', array( $this, '__give_start_updating' ) );

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, '__change_donations_label' ), 9999 );
			add_action( 'admin_menu', array( $this, '__register_menu' ), 9999 );
		}
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

		// Bailout.
		if ( empty( $menu ) || ! $this->get_total_update_count() ) {
			return;
		}

		foreach ( $menu as $index => $menu_item ) {
			if ( 'edit.php?post_type=give_forms' !== $menu_item[2] ) {
				continue;
			}

			$menu[ $index ][0] = sprintf(
				'%1$s <span class="update-plugins count-%2$s"><span class="plugin-count">%2$s%3$s</span></span>',
				__( 'Donations', 'give' ),
				$this->is_doing_updates() ?
					$this->get_db_update_processing_percentage() :
					$this->get_total_new_db_update_count(),
				$this->is_doing_updates() ? '%' : ''
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
		if ( ! $this->get_total_update_count() ) {
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
				'%1$s <span class="update-plugins count-%2$s"><span class="plugin-count">%2$s%3$s</span></span>',
				__( 'Updates', 'give' ),
				$this->is_doing_updates() ?
					$this->get_db_update_processing_percentage() :
					$this->get_total_new_db_update_count(),
				$this->is_doing_updates() ? '%' : ''
			),
			'manage_give_settings',
			'give-updates',
			array( $this, 'render_page' )
		);
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
	 * Delete resume updates
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __flush_resume_updates() {
		//delete_option( 'give_doing_upgrade' );
		update_option( 'give_version', preg_replace( '/[^0-9.].*/', '', GIVE_VERSION ) );

		// Reset counter.
		$this->step = $this->percentage = 0;
		++ $this->update;
	}


	/**
	 * Initialize updates
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return void
	 */
	public function __give_start_updating() {
		// Check permission.
		if (
			! current_user_can( 'manage_give_settings' ) ||
			$this->is_doing_updates()
		) {
			wp_send_json_error();
		}

		// @todo: validate nonce
		// @todo: set http method to post
		if ( empty( $_POST['run_db_update'] ) ) {
			wp_send_json_error();
		}

		$updates = $this->get_updates( 'database', 'new' );

		foreach ( $updates as $update ) {
			self::$background_updater->push_to_queue( $update );
		}

		add_option( 'give_db_update_count', count( $updates ), '', 'no' );
		self::$background_updater->save()->dispatch();

		wp_send_json_success();
	}


	/**
	 * This function handle ajax query for dn update status.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return string
	 */
	public function __give_db_updates_info() {
		$update_info   = get_option( 'give_doing_upgrade' );
		$response_type = '';

		if ( empty( $update_info ) && ! $this->get_pending_db_update_count() ) {
			$update_info   = array(
				'message'    => __( 'Database updated successfully.', 'give' ),
				'heading'    => __( 'Updates Completed.', 'give' ),
				'percentage' => 0,
			);
			$response_type = 'success';
		}
		$this->send_ajax_response( $update_info, $response_type );
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

		// Enable cache.
		Give_Cache::enable();

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
	 * @return bool|null
	 */
	public function is_parent_updates_completed( $update ) {
		// Bailout.
		if ( empty( $update['depend'] ) ) {
			return true;
		}

		$is_dependency_completed = true;

		foreach ( $update['depend'] as $depend ) {
			// Check if dependency is valid or not.
			if ( ! $this->has_valid_dependency( $update ) ) {
				$is_dependency_completed = null;
				break;
			}

			if ( ! give_has_upgrade_completed( $depend ) ) {
				$is_dependency_completed = false;
				break;
			}
		}

		return $is_dependency_completed;
	}

	/**
	 * Flag to check if DB updates running or not.
	 *
	 * @since  2.0
	 * @access public
	 * @return bool
	 */
	public function is_doing_updates() {
		return (bool) get_option( 'give_doing_upgrade' );
	}


	/**
	 * Check if update has valid dependency or not.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param $update
	 *
	 * @return bool
	 */
	public function has_valid_dependency( $update ) {
		$is_valid_dependency = true;
		$update_ids          = wp_list_pluck( $this->get_updates( 'database' ), 'id' );

		foreach ( $update['depend'] as $depend ) {
			// Check if dependency is valid or not.
			if ( ! in_array( $depend, $update_ids ) ) {
				$is_valid_dependency = false;
				break;
			}
		}

		return $is_valid_dependency;
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
	 * Get addon update count.
	 *
	 * @since  1.8.12
	 * @access public
	 * @return int
	 */
	public function get_total_plugin_update_count() {
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
	public function get_total_update_count() {
		$db_update_count     = $this->get_pending_db_update_count();
		$plugin_update_count = $this->get_total_plugin_update_count();

		return ( $db_update_count + $plugin_update_count );
	}

	/**
	 * Get total pending updates count
	 *
	 * @since  1.8.12
	 * @access public
	 *
	 * @return int
	 */
	public function get_pending_db_update_count() {
		return count( $this->get_updates( 'database', 'new' ) );
	}

	/**
	 * Get total updates count
	 *
	 * @since  1.8.18
	 * @access public
	 *
	 * @return int
	 */
	public function get_total_db_update_count() {
		return count( $this->get_updates( 'database', 'all' ) );
	}

	/**
	 * Get total new updates count
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_total_new_db_update_count() {
		return $this->is_doing_updates() ?
			get_option( 'give_db_update_count' ) :
			$this->get_pending_db_update_count();
	}

	/**
	 * Get total new updates count
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @return int
	 */
	public function get_running_db_update() {
		$current_update = get_option( 'give_doing_upgrade' );

		return $this->is_doing_updates() ?
			$current_update['update'] :
			1;
	}

	/**
	 * Get database update processing percentage.
	 *
	 * @since  2.0
	 * @access public
	 * @return float|int
	 */
	public function get_db_update_processing_percentage() {
		// Bailout.
		if( ! $this->get_total_new_db_update_count() ) {
			return 0;
		}

		$resume_update            = get_option( 'give_doing_upgrade' );
		$update_count_percentages = ( ( $this->get_running_db_update() - 1 ) / $this->get_total_new_db_update_count() ) * 100;
		$update_percentage_share  = ( 1 / $this->get_total_new_db_update_count() ) * 100;
		$upgrade_percentage       = ( ( $resume_update['percentage'] * $update_percentage_share ) / 100 );
		
		return $this->is_doing_updates() ?
			absint( $update_count_percentages + $upgrade_percentage ) :
			0;
	}
}

Give_Updates::get_instance()->setup();

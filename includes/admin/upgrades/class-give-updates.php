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
	private static $instance;

	/**
	 * Instance.
	 *
	 * @since
	 * @access public
	 * @var Give_Background_Updater
	 */
	public static $background_updater;

	/**
	 * Updates
	 *
	 * @since  1.8.12
	 * @access private
	 * @var array
	 */
	private $updates = [];

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
		$args_default = [
			'id'       => '',
			'version'  => '',
			'callback' => '',
		];

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
			$args['depend'] = [ $args['depend'] ];
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
		add_action( 'init', [ $this, '__register_upgrade' ], 9999 );
		add_action( 'give_set_upgrade_completed', [ $this, '__flush_resume_updates' ], 9999 );
		add_action( 'wp_ajax_give_db_updates_info', [ $this, '__give_db_updates_info' ] );
		add_action( 'wp_ajax_give_run_db_updates', [ $this, '__give_start_updating' ] );
		add_action( 'admin_init', [ $this, '__redirect_admin' ] );
		add_action( 'admin_init', [ $this, '__pause_db_update' ], - 1 );
		add_action( 'admin_init', [ $this, '__restart_db_update' ], - 1 );
		add_action( 'admin_notices', [ $this, '__show_notice' ] );
		add_action( 'give_restart_db_upgrade', [ $this, '__health_background_update' ] );

		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, '__change_donations_label' ], 9999 );
			add_action( 'admin_menu', [ $this, '__register_menu' ], 55 );
		}
	}

	/**
	 * Register plugin add-on updates.
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __register_plugin_addon_updates() {
		$addons         = give_get_plugins( [ 'only_premium_add_ons' => true ] );
		$plugin_updates = get_plugin_updates();

		foreach ( $addons as $key => $info ) {
			if ( empty( $plugin_updates[ $key ] ) ) {
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

		$is_update = ( $this->is_doing_updates() && ! self::$background_updater->is_paused_process() );

		foreach ( $menu as $index => $menu_item ) {
			if ( 'edit.php?post_type=give_forms' !== $menu_item[2] ) {
				continue;
			}

			$menu[ $index ][0] = sprintf(
				'%1$s <span class="update-plugins"><span class="plugin-count give-update-progress-count">%2$s%3$s</span></span>',
				__( 'Donations', 'give' ),
				$is_update ?
					$this->get_db_update_processing_percentage() :
					$this->get_total_update_count(),
				$is_update ? '%' : ''
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
					esc_html__( 'GiveWP Updates Complete', 'give' ),
					__( 'Updates', 'give' ),
					'manage_give_settings',
					'give-updates',
					[ $this, 'render_complete_page' ]
				);
			}

			return;
		}

		$is_update = ( $this->is_doing_updates() && ! self::$background_updater->is_paused_process() );

		// Upgrades
		add_submenu_page(
			'edit.php?post_type=give_forms',
			esc_html__( 'GiveWP Updates', 'give' ),
			sprintf(
				'%1$s <span class="update-plugins"%2$s><span class="plugin-count give-update-progress-count">%3$s%4$s</span></span>',
				__( 'Updates', 'give' ),
				isset( $_GET['give-pause-db-upgrades'] ) ? ' style="display:none;"' : '',
				$is_update ?
					$this->get_db_update_processing_percentage() :
					$this->get_total_update_count(),
				$is_update ? '%' : ''
			),
			'manage_give_settings',
			'give-updates',
			[ $this, 'render_page' ]
		);
	}


	/**
	 * Show update related notices
	 *
	 * @since  2.0
	 * @access public
	 */
	public function __redirect_admin() {
		// Show db upgrade completed notice.
		if (
			! wp_doing_ajax() &&
			current_user_can( 'manage_give_settings' ) &&
			get_option( 'give_show_db_upgrade_complete_notice' ) &&
			! isset( $_GET['give-db-update-completed'] )
		) {
			delete_option( 'give_show_db_upgrade_complete_notice' );

			wp_redirect( admin_url( 'edit.php?post_type=give_forms&page=give-updates&give-db-update-completed=give_db_upgrade_completed' ) );
			exit();
		}
	}


	/**
	 * Pause db upgrade
	 *
	 * @since  2.0.1
	 * @access public
	 *
	 * @param bool $force
	 *
	 * @return bool
	 */
	public function __pause_db_update( $force = false ) {
		// Bailout.
		if (
			! $force &&
			(
				wp_doing_ajax() ||
				! isset( $_GET['page'] ) ||
				'give-updates' !== $_GET['page'] ||
				! isset( $_GET['give-pause-db-upgrades'] ) ||
				self::$background_updater->is_paused_process()
			)

		) {
			return false;
		}

		delete_option( 'give_upgrade_error' );

		$this->__health_background_update( $this );
		$batch = self::$background_updater->get_all_batch();

		// Bailout: if batch is empty
		if ( empty( $batch->data ) ) {
			return false;
		}

		// Remove cache.
		Give_Background_Updater::flush_cache();

		// Do not stop background process immediately if task running.
		// @see Give_Background_Updater::lock_process
		if ( ! $force && self::$background_updater->is_process_running() ) {
			update_option( 'give_pause_upgrade', 1, false );

			return true;
		}

		update_option( 'give_paused_batches', $batch, false );
		delete_option( $batch->key );
		delete_site_transient( self::$background_updater->get_identifier() . '_process_lock' );
		wp_clear_scheduled_hook( self::$background_updater->get_cron_identifier() );

		Give()->logs->add( 'Update Pause', print_r( $batch, true ), 0, 'update' );

		/**
		 * Fire action when pause db updates
		 *
		 * @since 2.0.1
		 */
		do_action( 'give_pause_db_upgrade', $this );

		return true;
	}

	/**
	 * Restart db upgrade
	 *
	 * @since  2.0.1
	 * @access public
	 *
	 * @return bool
	 */
	public function __restart_db_update() {
		// Bailout.
		if (
			wp_doing_ajax() ||
			! isset( $_GET['page'] ) ||
			'give-updates' !== $_GET['page'] ||
			! isset( $_GET['give-restart-db-upgrades'] ) ||
			! self::$background_updater->is_paused_process()
		) {
			return false;
		}

		Give_Background_Updater::flush_cache();
		$batch = get_option( 'give_paused_batches' );

		if ( ! empty( $batch ) ) {
			wp_cache_delete( $batch->key, 'options' );
			update_option( $batch->key, $batch->data, false );

			delete_option( 'give_paused_batches' );

			Give()->logs->add( 'Update Restart', print_r( $batch, true ), 0, 'update' );

			/** Fire action when restart db updates
			 *
			 * @since 2.0.1
			 */
			do_action( 'give_restart_db_upgrade', $this );

			self::$background_updater->dispatch();
		}

		return true;
	}

	/**
	 * Health check for updates.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param Give_Updates $give_updates
	 */
	public function __health_background_update( $give_updates ) {
		if ( ! $this->is_doing_updates() ) {
			return;
		}

		Give_Background_Updater::flush_cache();

		/* @var stdClass $batch */
		$batch                = self::$background_updater->get_all_batch();
		$old_batch_update_ids = is_array( $batch->data ) ? wp_list_pluck( $batch->data, 'id' ) : [];
		$all_updates          = $give_updates->get_updates( 'database', 'all' );
		$all_update_ids       = wp_list_pluck( $all_updates, 'id' );
		$all_batch_update_ids = ! empty( $batch->data ) ? wp_list_pluck( $batch->data, 'id' ) : [];
		$log_data             = '';
		$doing_upgrade_args   = get_option( 'give_doing_upgrade' );

		if ( ! empty( $doing_upgrade_args ) ) {
			$log_data .= 'Doing update:' . "\n";
			$log_data .= print_r( $doing_upgrade_args, true ) . "\n";
		}

		/**
		 * Add remove upgrade from batch
		 */
		if ( ! empty( $batch->data ) ) {

			foreach ( $batch->data as $index => $update ) {
				$log_data = print_r( $update, true ) . "\n";

				if ( ! is_callable( $update['callback'] ) ) {
					$log_data .= 'Removing missing callback update: ' . "{$update['id']}\n";
					unset( $batch->data[ $index ] );
				} elseif ( give_has_upgrade_completed( $update['id'] ) ) {
					$log_data .= 'Removing already completed update: ' . "{$update['id']}\n";
					unset( $batch->data[ $index ] );
				}

				if ( ! empty( $update['depend'] ) ) {

					foreach ( $update['depend'] as $depend ) {
						if ( give_has_upgrade_completed( $depend ) ) {
							$log_data .= 'Completed update: ' . "{$depend}\n";
							continue;
						}

						if ( in_array( $depend, $all_update_ids ) && ! in_array( $depend, $all_batch_update_ids ) ) {
							$log_data .= 'Adding missing update: ' . "{$depend}\n";
							array_unshift( $batch->data, $all_updates[ array_search( $depend, $all_update_ids ) ] );
						}
					}
				}
			}
		}

		/**
		 * Add new upgrade to batch
		 */
		if ( $new_updates = $this->get_updates( 'database', 'new' ) ) {
			$all_batch_update_ids = ! empty( $batch->data ) ? wp_list_pluck( $batch->data, 'id' ) : [];

			foreach ( $new_updates as $index => $new_update ) {
				if ( give_has_upgrade_completed( $new_update['id'] ) || in_array( $new_update['id'], $all_batch_update_ids ) ) {
					unset( $new_updates[ $index ] );
				}
			}

			if ( ! empty( $new_updates ) ) {
				$log_data .= 'Adding new update: ' . "\n";
				$log_data .= print_r( $new_updates, true ) . "\n";

				$batch->data = array_merge( (array) $batch->data, $new_updates );
				update_option( 'give_db_update_count', ( absint( get_option( 'give_db_update_count' ) ) + count( $new_updates ) ), false );
			}
		}

		/**
		 * Fix batch
		 */
		if ( empty( $batch->data ) ) {
			// Complete batch if do not have any data to process.
			self::$background_updater->delete( $batch->key );

			if ( self::$background_updater->has_queue() ) {
				$this->__health_background_update( $this );
			} else {
				delete_site_transient( self::$background_updater->get_identifier() . '_process_lock' );
				wp_clear_scheduled_hook( self::$background_updater->get_cron_identifier() );

				self::$background_updater->complete();
			}
		} elseif ( array_diff( wp_list_pluck( $batch->data, 'id' ), $old_batch_update_ids ) ) {

			$log_data .= 'Updating batch' . "\n";
			$log_data .= print_r( $batch, true );

			if ( ! empty( $batch->key ) ) {
				wp_cache_delete( $batch->key, 'options' );
				update_option( $batch->key, $batch->data, false );
			} else {

				foreach ( $batch->data as $data ) {
					self::$background_updater->push_to_queue( $data );
				}

				self::$background_updater->save();
			}
		}

		/**
		 * Fix give_doing_upgrade option
		 */
		if ( $fresh_new_db_count = $this->get_total_new_db_update_count( true ) ) {
			update_option( 'give_db_update_count', $fresh_new_db_count, false );
		}

		$doing_upgrade_args['update']           = 1;
		$doing_upgrade_args['heading']          = sprintf( 'Update %s of %s', 1, $fresh_new_db_count );
		$doing_upgrade_args['total_percentage'] = $this->get_db_update_processing_percentage( true );

		// Remove already completed update from info.
		if (
			empty( $doing_upgrade_args['update_info'] )
			|| give_has_upgrade_completed( $doing_upgrade_args['update_info']['id'] )
		) {
			$doing_upgrade_args['update_info'] = current( array_values( $batch->data ) );
			$doing_upgrade_args['step']        = 1;
		}

		// Check if dependency completed or not.
		if ( isset( $doing_upgrade_args['update_info']['depend'] ) ) {
			foreach ( $doing_upgrade_args['update_info']['depend'] as $depend ) {
				if ( give_has_upgrade_completed( $depend ) ) {
					continue;
				}

				$doing_upgrade_args['update_info']      = $all_updates[ array_search( $depend, $all_update_ids ) ];
				$doing_upgrade_args['step']             = 1;
				$doing_upgrade_args['percentage']       = 0;
				$doing_upgrade_args['total_percentage'] = 0;

				break;
			}
		}

		if ( ! empty( $doing_upgrade_args['update_info'] ) ) {
			update_option( 'give_doing_upgrade', $doing_upgrade_args, false );

			$log_data .= 'Updated doing update:' . "\n";
			$log_data .= print_r( $doing_upgrade_args, true ) . "\n";
		}

		Give()->logs->add( 'Update Health Check', $log_data, 0, 'update' );
	}


	/**
	 * Show update related notices
	 *
	 * @since  2.0
	 * @access public
	 */
	public function __show_notice() {
		$current_screen = get_current_screen();
		$hide_on_pages  = [
			'give_forms_page_give-updates',
			'update-core',
			'give_forms_page_give-addons',
		];

		// Bailout.
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			return;
		}

		// Run DB updates.
		if ( ! empty( $_GET['give-run-db-update'] ) ) {
			$this->run_db_update();
		}

		// Bailout.
		if ( in_array( $current_screen->base, $hide_on_pages ) ) {
			return;
		}

		// Show notice if upgrade paused.
		if ( self::$background_updater->is_paused_process() ) {
			ob_start();

			$upgrade_error = get_option( 'give_upgrade_error' );
			if ( ! $upgrade_error ) : ?>
				<strong><?php _e( 'Database Update', 'give' ); ?></strong>
				&nbsp;&#8211;&nbsp;<?php _e( 'GiveWP needs to update your database to the latest version. The following process will make updates to your site\'s database. Please create a backup before proceeding.', 'give' ); ?>
				<br>
				<br>
				<a href="<?php echo esc_url( add_query_arg( [ 'give-restart-db-upgrades' => 1 ], admin_url( 'edit.php?post_type=give_forms&page=give-updates' ) ) ); ?>" class="button button-primary give-restart-updater-btn">
					<?php _e( 'Restart the updater', 'give' ); ?>
				</a>
			<?php else : ?>
				<strong><?php _e( 'Database Update', 'give' ); ?></strong>
				&nbsp;&#8211;&nbsp;<?php _e( 'An unexpected issue occurred during the database update which caused it to stop automatically. Please contact support for assistance.', 'give' ); ?>
				<a href="<?php echo esc_url( 'http://docs.givewp.com/troubleshooting-db-updates' ); ?>" target="_blank"><?php _e( 'Read More', 'give' ); ?> &raquo;</a>
				<?php
			endif;
			$desc_html = ob_get_clean();

			Give()->notices->register_notice(
				[
					'id'          => 'give_upgrade_db',
					'type'        => 'error',
					'dismissible' => false,
					'description' => $desc_html,
				]
			);
		}

		// Bailout if doing upgrades.
		if ( $this->is_doing_updates() ) {
			return;
		}

		// Show db upgrade completed notice.
		if ( ! empty( $_GET['give-db-update-completed'] ) ) {
			Give()->notices->register_notice(
				[
					'id'          => 'give_db_upgrade_completed',
					'type'        => 'updated',
					'description' => __( 'GiveWP database updates completed successfully. Thank you for updating to the latest version!', 'give' ),
					'show'        => true,
				]
			);

			// Start update.
		} elseif ( ! empty( $_GET['give-run-db-update'] ) ) {
			$this->run_db_update();

			// Show run the update notice.
		} elseif ( $this->get_total_new_db_update_count() ) {
			ob_start();
			?>
			<p>
				<strong><?php _e( 'Database Update', 'give' ); ?></strong>
				&nbsp;&#8211;&nbsp;<?php _e( 'GiveWP needs to update your database to the latest version. The following process will make updates to your site\'s database. Please create a complete backup before proceeding.', 'give' ); ?>
			</p>
			<p class="submit">
				<a href="<?php echo esc_url( add_query_arg( [ 'give-run-db-update' => 1 ], admin_url( 'edit.php?post_type=give_forms&page=give-updates' ) ) ); ?>" class="button button-primary give-run-update-now">
					<?php _e( 'Run the updater', 'give' ); ?>
				</a>
			</p>
			<?php
			$desc_html = ob_get_clean();

			Give()->notices->register_notice(
				[
					'id'          => 'give_upgrade_db',
					'type'        => 'updated',
					'dismissible' => false,
					'description' => $desc_html,
				]
			);
		}
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
	 * Run database upgrades
	 *
	 * @since  2.0
	 * @access private
	 */
	private function run_db_update() {
		// Bailout.
		if ( $this->is_doing_updates() || ! $this->get_total_new_db_update_count() ) {
			return;
		}

		$updates = $this->get_updates( 'database', 'new' );

		foreach ( $updates as $update ) {
			self::$background_updater->push_to_queue( $update );
		}

		add_option( 'give_db_update_count', count( $updates ), '', false );

		add_option(
			'give_doing_upgrade',
			[
				'update_info'      => $updates[0],
				'step'             => 1,
				'update'           => 1,
				'heading'          => sprintf( 'Update %s of %s', 1, count( $updates ) ),
				'percentage'       => 0,
				'total_percentage' => 0,
			],
			'',
			false
		);

		self::$background_updater->save()->dispatch();
	}


	/**
	 * Delete resume updates
	 *
	 * @since  1.8.12
	 * @access public
	 */
	public function __flush_resume_updates() {
		$this->step = $this->percentage = 0;

		$this->update = ( $this->get_total_db_update_count() > $this->update ) ?
			( $this->update + 1 ) :
			$this->update;
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
			// Run update via ajax
			self::$background_updater->dispatch();

			wp_send_json_error();
		}

		// @todo: validate nonce
		// @todo: set http method to post
		if ( empty( $_POST['run_db_update'] ) ) {
			wp_send_json_error();
		}

		$this->run_db_update();

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
		// Check permission.
		if ( ! current_user_can( 'manage_give_settings' ) ) {
			give_die();
		}

		$update_info   = get_option( 'give_doing_upgrade' );
		$response_type = '';

		if ( self::$background_updater->is_paused_process() ) {
			$update_info = [
				'message'    => __( 'The updates have been paused.', 'give' ),
				'heading'    => '',
				'percentage' => 0,
			];

			if ( get_option( 'give_upgrade_error' ) ) {
				$update_info['message'] = __( 'An unexpected issue occurred during the database update which caused it to stop automatically. Please contact support for assistance.', 'give' );
			}

			$response_type = 'error';

		} elseif ( empty( $update_info ) || ! $this->get_total_new_db_update_count( true ) ) {
			$update_info   = [
				'message'    => __( 'GiveWP database updates completed successfully. Thank you for updating to the latest version!', 'give' ),
				'heading'    => __( 'Updates Completed.', 'give' ),
				'percentage' => 0,
			];
			$response_type = 'success';

			delete_option( 'give_show_db_upgrade_complete_notice' );
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
		$default = [
			'message'    => '',
			'heading'    => '',
			'percentage' => 0,
			'step'       => 0,
			'update'     => 0,
		];

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
				wp_send_json(
					[
						'data' => $data,
					]
				);
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

		// Check if dependency is valid or not.
		if ( ! $this->has_valid_dependency( $update ) ) {
			return null;
		}

		$is_dependency_completed = true;

		foreach ( $update['depend'] as $depend ) {

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
		return (bool) Give_Cache_Setting::get_option( 'give_doing_upgrade' );
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
		// $update_ids          = wp_list_pluck( $this->get_updates( 'database', 'all' ), 'id' );
		//
		// foreach ( $update['depend'] as $depend ) {
		// Check if dependency is valid or not.
		// if ( ! in_array( $depend, $update_ids ) ) {
		// $is_valid_dependency = false;
		// break;
		// }
		// }

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
		$updates = ! empty( $this->updates[ $update_type ] ) ? $this->updates[ $update_type ] : [];

		// Bailout.
		if ( empty( $updates ) ) {
			return $updates;
		}

		switch ( $status ) {
			case 'new':
				// Remove already completed updates.
				wp_cache_delete( 'give_completed_upgrades', 'options' );
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
	 * @param bool $refresh
	 *
	 * @return int
	 */
	public function get_total_new_db_update_count( $refresh = false ) {
		$update_count = $this->is_doing_updates() && ! $refresh ?
			get_option( 'give_db_update_count' ) :
			$this->get_pending_db_update_count();

		return $update_count;
	}

	/**
	 * Get total new updates count
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param bool $refresh
	 *
	 * @return int
	 */
	public function get_running_db_update( $refresh = false ) {
		$current_update = 1;

		if ( $this->is_doing_updates() && ! $refresh ) {
			$current_update = get_option( 'give_doing_upgrade' );
			$current_update = $current_update['update'];
		}

		return $current_update;
	}

	/**
	 * Get database update processing percentage.
	 *
	 * @since  2.0
	 * @access public
	 *
	 * @param bool $refresh
	 *
	 * @return float|int
	 */
	public function get_db_update_processing_percentage( $refresh = false ) {
		// Bailout.
		if ( ! $this->get_total_new_db_update_count( $refresh ) ) {
			return 0;
		}

		$resume_update            = get_option( 'give_doing_upgrade' );
		$update_count_percentages = ( ( $this->get_running_db_update( $refresh ) - 1 ) / $this->get_total_new_db_update_count( $refresh ) ) * 100;
		$update_percentage_share  = ( 1 / $this->get_total_new_db_update_count() ) * 100;
		$upgrade_percentage       = ( ( $resume_update['percentage'] * $update_percentage_share ) / 100 );

		$final_percentage = $update_count_percentages + $upgrade_percentage;

		return $this->is_doing_updates() ?
			( absint( $final_percentage ) ?
				absint( $final_percentage ) :
				round( $final_percentage, 2 )
			) :
			0;
	}


	/**
	 * Get all update ids.
	 *
	 * @since 2.0.3
	 *
	 * @return array
	 */
	public function get_update_ids() {
		$all_updates    = $this->get_updates( 'database', 'all' );
		$all_update_ids = wp_list_pluck( $all_updates, 'id' );

		return $all_update_ids;
	}

	/**
	 * Get offset count
	 *
	 * @since  2.0.5
	 * @access public
	 *
	 * @param int $process_item_count
	 *
	 * @return float|int
	 */
	public function get_offset( $process_item_count ) {
		return ( 1 === $this->step ) ?
			0 :
			( $this->step - 1 ) * $process_item_count;
	}
}

Give_Updates::get_instance()->setup();

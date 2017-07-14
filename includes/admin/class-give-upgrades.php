<?php

/**
 * Created by PhpStorm.
 * User: ravinderkumar
 * Date: 14/07/17
 * Time: 3:27 PM
 */
class Give_Upgrades {
	/**
	 * Instance.
	 *
	 * @since
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.8.12
	 * @access private
	 *
	 * @param Give_Upgrades .
	 */
	private function __construct() {
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
		if ( $this->get_update_count() ) {
			add_action( 'admin_init', array( $this, 'change_donations_label' ), 9999 );
			add_action( 'admin_menu', array( $this, 'register_menu' ), 9999 );
		}
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

		if ( empty( $menu ) ) {
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
			'give-upgrades',
			'give_upgrades_screen'
		);
	}

	/**
	 * Get tottal updates count
	 *
	 * @since  1.8.12
	 * @access public
	 * @return int
	 */
	public function get_update_count() {
		return 4;
	}
}

Give_Upgrades::get_instance()->setup_hooks();
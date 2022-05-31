<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 2.19.0 - migrated from give-recurring
 */
class Give_Recurring_Cache {
	/**
	 * Instance.
	 *
	 * @since  1.6
	 * @access private
	 * @var
	 */
	static private $instance;

	/**
	 * List of cache groups
	 *
	 * @since 1.6
	 *
	 * @var array
	 */
	private $groups = array(
		'give-subscriptions-db-query',
		'give-subscriptions'
	);

	/**
	 * List of cache groups
	 *
	 * @since 1.6
	 *
	 * @var Give_Subscriptions_DB
	 */
	private $sub_db;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.6
	 * @access private
	 */
	private function __construct() {
	}

	/**
	 * Setup hooks
	 *
	 * @since 1.6
	 */
	public function setup() {
		$this->sub_db = new Give_Subscriptions_DB();

		add_action( 'give_subscription_inserted', array( $this, 'flush_on_subscription_insert' ) );
		add_action( 'give_subscription_deleted', array( $this, 'flush_on_subscription_delete' ), 10, 2 );
		add_action( 'give_cache_filter_group_name', array( $this, 'filter_group_name' ), 999, 2 );
		add_action( 'give_deleted_give-donations_cache', array( $this, 'flush_on_donation_edit' ), 10 );
		add_action( 'before_delete_post', array( $this, 'flush_on_donation_delete' ), 10 );
	}

	/**
	 * Filter the group name
	 *
	 * @param $filtered_group_name
	 * @param $group
	 *
	 *
	 * @return string
	 */
	public function filter_group_name( $filtered_group_name, $group ) {
		if ( in_array( $group, $this->groups ) ) {
			$incrementer = Give_Cache::get_instance()->get_incrementer( false, "{$group}-incrementer" );

			$currenct_blog_id    = get_current_blog_id();
			$filtered_group_name = "{$group}_{$currenct_blog_id}_{$incrementer}";
		}

		return $filtered_group_name;
	}


	/**
	 * Get instance.
	 *
	 * @since  1.6
	 * @access public
	 * @return Give_Recurring_Cache
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Set subscriptions cache
	 *
	 * @since 1.6
	 *
	 * @param $cache_key
	 * @param $data
	 *
	 * @return bool
	 */
	public static function set_subscription( $cache_key, $data ) {
		return Give_Cache::set_group( $cache_key, $data, 'give-subscriptions' );
	}

	/**
	 * Get subscriptions cache
	 *
	 * @since 1.6
	 *
	 * @param $cache_key
	 *
	 * @return mixed
	 */
	public static function get_subscription( $cache_key ) {
		return Give_Cache::get_group( $cache_key, 'give-subscriptions' );
	}

	/**
	 * Set subscriptions db query cache
	 *
	 * @since 1.6
	 *
	 * @param $cache_key
	 * @param $data
	 *
	 * @return bool
	 */
	public static function set_db_query( $cache_key, $data ) {
		return Give_Cache::set_group( $cache_key, $data, 'give-subscriptions-db-query' );
	}

	/**
	 * Get subscriptions query cache
	 *
	 * @since 1.6
	 *
	 * @param $cache_key
	 *
	 * @return bool
	 */
	public static function get_db_query( $cache_key ) {
		return Give_Cache::get_group( $cache_key, 'give-subscriptions-db-query' );
	}

	/**
	 * Delete subscription db query cache when new subscription creates
	 *
	 * @since 1.6
	 */
	public function flush_on_subscription_insert() {
		Give_Cache::get_instance()->get_incrementer( true, 'give-subscriptions-db-query-incrementer' );
	}

	/**
	 * Delete subscription cache when subscription deletes
	 *
	 * @since 1.6
	 *
	 * @param $deleted
	 * @param $subscription_id
	 */
	public function flush_on_subscription_delete( $deleted, $subscription_id ) {
		// Bailout.
		if ( ! $deleted ) {
			return;
		}

		Give_Cache::delete_group( $subscription_id, 'give-subscriptions' );
		Give_Cache::get_instance()->get_incrementer( true, 'give-subscriptions-db-query-incrementer' );
	}

	/**
	 * Delete subscription cache when subscription updates
	 *
	 * @since 1.6
	 *
	 * @param $updated
	 * @param $subscription_id
	 * @param $data
	 * @param $where
	 */
	public function flush_on_subscription_update( $updated, $subscription_id, $data, $where ) {
		// Bailout.
		if ( ! $updated ) {
			return;
		}

		if ( empty( $where ) ) {
			Give_Cache::delete_group( $subscription_id, 'give-subscriptions' );
		} else {
			Give_Cache::get_instance()->get_incrementer( true, 'give-subscriptions-incrementer' );
		}

		Give_Cache::get_instance()->get_incrementer( true, 'give-subscriptions-db-query-incrementer' );
	}


	/**
	 * Delete subscription cache when donation update/insert
	 *
	 * @since 1.6
	 *
	 * @param int $donation_id
	 */
	public function flush_on_donation_edit( $donation_id ) {
		$donation = get_post( $donation_id );

		// Bailout.
		if ( ! $donation instanceof WP_Post ) {
			return;
		}

		$subscription_id = $donation->post_parent
			? $this->sub_db->get_column_by( 'id', 'parent_payment_id', $donation->post_parent )
			: give_get_meta( $donation_id, 'subscription_id', true );

		if ( $subscription_id ) {
			Give_Cache::delete_group( $subscription_id, 'give-subscriptions' );
			Give_Cache::get_instance()->get_incrementer( true, 'give-subscriptions-db-query-incrementer' );
		}
	}

	/**
	 * Delete subscription cache when donation delete
	 *
	 * @since 1.6
	 *
	 * @param int $donation_id
	 */
	public function flush_on_donation_delete( $donation_id ) {
		$this->flush_on_donation_edit( $donation_id );
	}
}

Give_Recurring_Cache::get_instance();

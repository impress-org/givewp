<?php
/**
 * Payments Query
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Payments_Query Class
 *
 * This class is for retrieving payments data.
 *
 * Payments can be retrieved for date ranges and pre-defined periods.
 *
 * @since 1.0
 */
class Give_Payments_Query extends Give_Stats {

	/**
	 * The args to pass to the give_get_payments() query
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    array
	 */
	public $args = array();

	/**
	 * The payments found based on the criteria set
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @var    array
	 */
	public $payments = array();

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  $args array The array of arguments that can be passed in and used for setting up this payment query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'          => 'payments', // Use 'posts' to get standard post objects
			'post_type'       => array( 'give_payment' ),
			'start_date'      => false,
			'end_date'        => false,
			'number'          => 20,
			'page'            => null,
			'orderby'         => 'ID',
			'order'           => 'DESC',
			'user'            => null,
			'status'          => give_get_payment_status_keys(),
			'meta_key'        => null,
			'year'            => null,
			'month'           => null,
			'day'             => null,
			's'               => null,
			'search_in_notes' => false,
			'children'        => false,
			'fields'          => null,
			'give_forms'      => null,
		);

		$this->args = wp_parse_args( $args, $defaults );

		$this->init();
	}

	/**
	 * Set a query variable.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $query_var
	 * @param $value
	 */
	public function __set( $query_var, $value ) {
		if ( in_array( $query_var, array( 'meta_query', 'tax_query' ) ) ) {
			$this->args[ $query_var ][] = $value;
		} else {
			$this->args[ $query_var ] = $value;
		}
	}

	/**
	 * Unset a query variable.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $query_var
	 */
	public function __unset( $query_var ) {
		unset( $this->args[ $query_var ] );
	}

	/**
	 * Modify the query/query arguments before we retrieve payments.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function init() {

		add_action( 'give_pre_get_payments', array( $this, 'date_filter_pre' ) );
		add_action( 'give_post_get_payments', array( $this, 'date_filter_post' ) );

		add_action( 'give_pre_get_payments', array( $this, 'orderby' ) );
		add_filter( 'posts_orderby', array( $this, 'custom_orderby' ), 10, 2 );
		add_action( 'give_pre_get_payments', array( $this, 'status' ) );
		add_action( 'give_pre_get_payments', array( $this, 'month' ) );
		add_action( 'give_pre_get_payments', array( $this, 'per_page' ) );
		add_action( 'give_pre_get_payments', array( $this, 'page' ) );
		add_action( 'give_pre_get_payments', array( $this, 'user' ) );
		add_action( 'give_pre_get_payments', array( $this, 'search' ) );
		add_action( 'give_pre_get_payments', array( $this, 'mode' ) );
		add_action( 'give_pre_get_payments', array( $this, 'children' ) );
		add_action( 'give_pre_get_payments', array( $this, 'give_forms' ) );
	}

	/**
	 * Retrieve payments.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return object
	 */
	public function get_payments() {

		/**
		 * Fires before retrieving payments.
		 *
		 * @since 1.0
		 *
		 * @param Give_Payments_Query $this Payments query object.
		 */
		do_action( 'give_pre_get_payments', $this );

		$query = new WP_Query( $this->args );

		$custom_output = array(
			'payments',
			'give_payments',
		);

		if ( ! in_array( $this->args['output'], $custom_output ) ) {
			return $query->posts;
		}

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();

				$payment_id = get_post()->ID;
				$payment    = new Give_Payment( $payment_id );

				$this->payments[] = apply_filters( 'give_payment', $payment, $payment_id, $this );
			}

			wp_reset_postdata();
		}

		/**
		 * Fires after retrieving payments.
		 *
		 * @since 1.0
		 *
		 * @param Give_Payments_Query $this Payments query object.
		 */
		do_action( 'give_post_get_payments', $this );

		return $this->payments;
	}

	/**
	 * If querying a specific date, add the proper filters.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function date_filter_pre() {
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		$this->setup_dates( $this->args['start_date'], $this->args['end_date'] );

		add_filter( 'posts_where', array( $this, 'payments_where' ) );
	}

	/**
	 * If querying a specific date, remove filters after the query has been run
	 * to avoid affecting future queries.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function date_filter_post() {
		if ( ! ( $this->args['start_date'] || $this->args['end_date'] ) ) {
			return;
		}

		remove_filter( 'posts_where', array( $this, 'payments_where' ) );
	}

	/**
	 * Post Status
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function status() {
		if ( ! isset ( $this->args['status'] ) ) {
			return;
		}

		$this->__set( 'post_status', $this->args['status'] );
		$this->__unset( 'status' );
	}

	/**
	 * Current Page
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function page() {
		if ( ! isset ( $this->args['page'] ) ) {
			return;
		}

		$this->__set( 'paged', $this->args['page'] );
		$this->__unset( 'page' );
	}

	/**
	 * Posts Per Page
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function per_page() {

		if ( ! isset( $this->args['number'] ) ) {
			return;
		}

		if ( $this->args['number'] == - 1 ) {
			$this->__set( 'nopaging', true );
		} else {
			$this->__set( 'posts_per_page', $this->args['number'] );
		}

		$this->__unset( 'number' );
	}

	/**
	 * Current Month
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function month() {
		if ( ! isset ( $this->args['month'] ) ) {
			return;
		}

		$this->__set( 'monthnum', $this->args['month'] );
		$this->__unset( 'month' );
	}

	/**
	 * Order by
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function orderby() {
		switch ( $this->args['orderby'] ) {
			case 'amount' :
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_give_payment_total' );
				break;

			case 'status' :
				$this->__set( 'orderby', 'post_status' );
				break;

			case 'donation_form' :
				$this->__set( 'orderby', 'meta_value' );
				$this->__set( 'meta_key', '_give_payment_form_title' );
				break;

			default :
				$this->__set( 'orderby', $this->args['orderby'] );
				break;
		}
	}

	/**
	 * Custom orderby.
	 * Note: currently custom sorting is only used for donation listing page.
	 *
	 * @since  1.8
	 * @access public
	 *
	 * @param string   $order
	 * @param WP_Query $query
	 *
	 * @return mixed
	 */
	public function custom_orderby( $order, $query ) {
		$post_types = is_array( $query->query['post_type'] ) ? $query->query['post_type'] : array( $query->query['post_type'] );
		if ( ! in_array( 'give_payment', $post_types ) || is_array( $query->query['orderby'] ) ) {
			return $order;
		}

		switch ( $query->query['orderby'] ) {
			case 'post_status':
				$order = 'wp_posts.post_status ' . strtoupper( $query->query['order'] );
				break;
		}

		return $order;
	}

	/**
	 * Specific User
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function user() {
		if ( is_null( $this->args['user'] ) ) {
			return;
		}

		if ( is_numeric( $this->args['user'] ) ) {
			$user_key = '_give_payment_user_id';
		} else {
			$user_key = '_give_payment_user_email';
		}

		$this->__set( 'meta_query', array(
			'key'   => $user_key,
			'value' => $this->args['user'],
		) );
	}

	/**
	 * Search
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function search() {

		if ( ! isset( $this->args['s'] ) ) {
			return;
		}

		$search = trim( $this->args['s'] );

		if ( empty( $search ) ) {
			return;
		}

		$is_email = is_email( $search ) || strpos( $search, '@' ) !== false;
		$is_user  = strpos( $search, strtolower( 'user:' ) ) !== false;

		if ( ! empty( $this->args['search_in_notes'] ) ) {

			$notes = give_get_payment_notes( 0, $search );

			if ( ! empty( $notes ) ) {

				$payment_ids = wp_list_pluck( (array) $notes, 'comment_post_ID' );

				$this->__set( 'post__in', $payment_ids );
			}

			$this->__unset( 's' );

		} elseif ( $is_email || strlen( $search ) == 32 ) {

			$key         = $is_email ? '_give_payment_user_email' : '_give_payment_purchase_key';
			$search_meta = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => 'LIKE',
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( $is_user ) {

			$search_meta = array(
				'key'   => '_give_payment_user_id',
				'value' => trim( str_replace( 'user:', '', strtolower( $search ) ) ),
			);

			$this->__set( 'meta_query', $search_meta );

			if ( give_get_option( 'enable_sequential' ) ) {

				$search_meta = array(
					'key'     => '_give_payment_number',
					'value'   => $search,
					'compare' => 'LIKE',
				);

				$this->__set( 'meta_query', $search_meta );

				$this->args['meta_query']['relation'] = 'OR';

			}

			$this->__unset( 's' );

		} elseif (
			give_get_option( 'enable_sequential' ) &&
			(
				false !== strpos( $search, give_get_option( 'sequential_prefix' ) ) ||
				false !== strpos( $search, give_get_option( 'sequential_postfix' ) )
			)
		) {

			$search_meta = array(
				'key'     => '_give_payment_number',
				'value'   => $search,
				'compare' => 'LIKE',
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( is_numeric( $search ) ) {

			$post = get_post( $search );

			if ( is_object( $post ) && $post->post_type == 'give_payment' ) {

				$arr   = array();
				$arr[] = $search;
				$this->__set( 'post__in', $arr );
				$this->__unset( 's' );
			}
		} elseif ( '#' == substr( $search, 0, 1 ) ) {

			$search = str_replace( '#:', '', $search );
			$search = str_replace( '#', '', $search );
			$this->__set( 'give_forms', $search );
			$this->__unset( 's' );

		} else {
			$this->__set( 's', $search );

		}

	}

	/**
	 * Payment Mode
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function mode() {
		if ( empty( $this->args['mode'] ) || $this->args['mode'] == 'all' ) {
			$this->__unset( 'mode' );

			return;
		}

		$this->__set( 'meta_query', array(
			'key'   => '_give_payment_mode',
			'value' => $this->args['mode'],
		) );
	}

	/**
	 * Children
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function children() {
		if ( empty( $this->args['children'] ) ) {
			$this->__set( 'post_parent', 0 );
		}
		$this->__unset( 'children' );
	}

	/**
	 * Specific Give Form
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function give_forms() {

		if ( empty( $this->args['give_forms'] ) ) {
			return;
		}

		$compare = '=';

		if ( is_array( $this->args['give_forms'] ) ) {
			$compare = 'IN';
		}

		$this->__set( 'meta_query', array(
			array(
				'key'     => '_give_payment_form_id',
				'value'   => $this->args['give_forms'],
				'compare' => $compare,
			),
		) );

		$this->__unset( 'give_forms' );

	}

}

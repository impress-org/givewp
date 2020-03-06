<?php
/**
 * Payments Query
 *
 * @package     Give
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2016, GiveWP
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
	 * Preserve args
	 *
	 * @since  1.8.17
	 * @access public
	 *
	 * @var    array
	 */
	public $_args = array();

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
			'output'          => 'payments',
			'post_type'       => array( 'give_payment' ),
			'start_date'      => false,
			'end_date'        => false,
			'number'          => 20,
			'page'            => null,
			'orderby'         => 'ID',
			'order'           => 'DESC',
			'user'            => null, // deprecated, use donor
			'donor'           => null,
			'status'          => give_get_payment_status_keys(),
			'meta_key'        => null,
			'year'            => null,
			'month'           => null,
			'day'             => null,
			's'               => null,
			'search_in_notes' => false,
			'children'        => false,
			'fields'          => null,
			'gateway'         => null,
			'give_forms'      => null,
			'offset'          => null,

			// Currently these params only works with get_payment_by_group
			'group_by'        => '',
			'count'           => false,
		);

		// We do not want WordPress to handle meta cache because WordPress stores in under `post_meta` key and cache object while we want it under `donation_meta`.
		// Similar for term cache
		$args['update_post_meta_cache'] = false;

		$this->args = $this->_args = wp_parse_args( $args, $defaults );

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
	}


	/**
	 * Set query filter.
	 *
	 * @since  1.8.9
	 * @access private
	 */
	private function set_filters() {
		// Reset param to apply filters.
		// While set filters $args will get override and multiple get_payments call will not work.
		$this->args = $this->_args;

		// Whitelist order.
		$this->args['order'] = in_array( strtoupper( $this->args['order'] ), array( 'ASC', 'DESC' ) ) ? $this->args['order'] : 'DESC';

		$this->date_filter_pre();
		$this->orderby();
		$this->status();
		$this->month();
		$this->per_page();
		$this->page();
		$this->user();
		$this->donor();
		$this->search();
		$this->mode();
		$this->children();
		$this->give_forms();
		$this->gateway_filter();

		add_filter( 'posts_orderby', array( $this, 'custom_orderby' ), 10, 2 );

		/**
		 * Fires after setup filters.
		 *
		 * @since 1.0
		 *
		 * @param Give_Payments_Query $this Payments query object.
		 */
		do_action( 'give_pre_get_payments', $this );
	}

	/**
	 * Unset query filter.
	 *
	 * @since  1.8.9
	 * @access private
	 */
	private function unset_filters() {
		remove_filter( 'posts_orderby', array( $this, 'custom_orderby' ) );

		/**
		 * Fires after retrieving payments.
		 *
		 * @since 1.0
		 *
		 * @param Give_Payments_Query $this Payments query object.
		 */
		do_action( 'give_post_get_payments', $this );
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
	 * @return array
	 */
	public function get_payments() {
		global $post;

		$results        = array();
		$this->payments = array();
		$cache_key      = Give_Cache::get_key( 'give_payment_query', $this->args, false );
		$this->payments = Give_Cache::get_db_query( $cache_key );

		// Return cached result.
		if ( ! is_null( $this->payments ) ) {
			return $this->payments;
		}

		// Modify the query/query arguments before we retrieve payments.
		$this->set_filters();

		/* @var WP_Query $query */
		$query = new WP_Query( $this->args );

		$custom_output = array(
			'payments',
			'give_payments',
		);

		if ( $query->have_posts() ) {

			// Update meta cache only if query is not for all donations.
			// @see https://github.com/impress-org/give/issues/4104
			if (
				( isset( $this->args['nopaging'] ) && true !== (bool) $this->args['nopaging'] )
				|| ( isset( $this->args['posts_per_page'] ) && 0 < $this->args['posts_per_page'] )
			) {
				self::update_meta_cache( wp_list_pluck( $query->posts, 'ID' ) );
			}

			if ( ! in_array( $this->args['output'], $custom_output ) ) {
				$results = $query->posts;

			} else {
				$previous_post = $post;

				while ( $query->have_posts() ) {
					$query->the_post();

					$payment_id = get_post()->ID;
					$payment    = new Give_Payment( $payment_id );

					$this->payments[] = apply_filters( 'give_payment', $payment, $payment_id, $this );
				}

				wp_reset_postdata();

				// Prevent nest loop from producing unexpected results.
				if ( $previous_post instanceof WP_Post ) {
					$post = $previous_post;
					setup_postdata( $post );
				}

				$results = $this->payments;
			}
		}

		Give_Cache::set_db_query( $cache_key, $results );

		// Remove query filters after we retrieve payments.
		$this->unset_filters();

		return $results;
	}

	/**
	 * Get payments by group
	 *
	 * @since  1.8.17
	 * @access public
	 *
	 * @return array
	 */
	public function get_payment_by_group() {
		global $wpdb;

		$allowed_groups = array( 'post_status' );
		$result         = array();

		if ( in_array( $this->args['group_by'], $allowed_groups ) ) {
			// Set only count in result.
			if ( $this->args['count'] ) {

				$this->set_filters();

				$new_results = $wpdb->get_results( $this->get_sql(), ARRAY_N );

				$this->unset_filters();

				foreach ( $new_results as $results ) {
					$result[ $results[0] ] = $results[1];
				}

				switch ( $this->args['group_by'] ) {
					case 'post_status':
						/* @var Give_Payment $donation */
						foreach ( give_get_payment_status_keys() as $status ) {
							if ( ! isset( $result[ $status ] ) ) {
								$result[ $status ] = 0;
							}
						}

						break;
				}
			} else {
				$donations = $this->get_payments();

				/* @var $donation Give_Payment */
				foreach ( $donations as $donation ) {
					$result[ $donation->{$this->args['group_by']} ][] = $donation;
				}
			}
		}

		/**
		 * Filter the result
		 *
		 * @since 1.8.17
		 */
		return apply_filters( 'give_get_payment_by_group', $result, $this );
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

		$is_start_date = property_exists( __CLASS__, 'start_date' );
		$is_end_date   = property_exists( __CLASS__, 'end_date' );

		if ( $is_start_date || $is_end_date ) {
			$date_query = array();

			if ( $is_start_date && ! is_wp_error( $this->start_date ) ) {
				$date_query['after'] = date( 'Y-m-d H:i:s', $this->start_date );
			}

			if ( $is_end_date && ! is_wp_error( $this->end_date ) ) {
				$date_query['before'] = date( 'Y-m-d H:i:s', $this->end_date );
			}

			// Include Start Date and End Date while querying.
			$date_query['inclusive'] = true;

			$this->__set( 'date_query', $date_query );

		}
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
		if ( ! isset( $this->args['status'] ) ) {
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
		if ( ! isset( $this->args['page'] ) ) {
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
		if ( ! isset( $this->args['month'] ) ) {
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
			case 'amount':
				$this->__set( 'orderby', 'meta_value_num' );
				$this->__set( 'meta_key', '_give_payment_total' );
				break;

			case 'status':
				$this->__set( 'orderby', 'post_status' );
				break;

			case 'donation_form':
				$this->__set( 'orderby', 'meta_value' );
				$this->__set( 'meta_key', '_give_payment_form_title' );
				break;

			default:
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

		if ( ! empty( $query->query['post_type'] ) ) {
			$post_types = is_array( $query->query['post_type'] ) ? $query->query['post_type'] : array( $query->query['post_type'] );

			if ( ! in_array( 'give_payment', $post_types ) || ! isset( $query->query['orderby'] ) || is_array( $query->query['orderby'] ) ) {
				return $order;
			}

			global $wpdb;
			switch ( $query->query['orderby'] ) {
				case 'post_status':
					$order = $wpdb->posts . '.post_status ' . strtoupper( $query->query['order'] );
					break;
			}
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

		$args = array();

		if ( is_numeric( $this->args['user'] ) ) {
			// Backward compatibility: user donor param to get payment attached to donor instead of user
			$donor_id = Give()->donors->get_column_by( 'id', 'user_id', $this->args['user'] );

			$args = array(
				'key'   => '_give_payment_donor_id',
				'value' => $donor_id ?: -1,
			);
		} elseif ( is_email( $this->args['user'] ) ) {
			$args = array(
				'key'   => '_give_payment_donor_email',
				'value' => $this->args['user'],
			);
		}

		$this->__set( 'meta_query', $args );
	}

	/**
	 * Specific donor id
	 *
	 * @access  public
	 * @since   1.8.9
	 * @return  void
	 */
	public function donor() {
		if ( is_null( $this->args['donor'] ) || ! is_numeric( $this->args['donor'] ) ) {
			return;
		}

		$donor_meta_type = Give()->donor_meta->meta_type;

		$this->__set(
			'meta_query',
			array(
				'key'   => "_give_payment_{$donor_meta_type}_id",
				'value' => (int) $this->args['donor'],
			)
		);
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

			$key         = $is_email ? '_give_payment_donor_email' : '_give_payment_purchase_key';
			$search_meta = array(
				'key'     => $key,
				'value'   => $search,
				'compare' => 'LIKE',
			);

			$this->__set( 'meta_query', $search_meta );
			$this->__unset( 's' );

		} elseif ( $is_user ) {

			$search_meta = array(
				'key'   => '_give_payment_donor_id',
				'value' => trim( str_replace( 'user:', '', strtolower( $search ) ) ),
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

		} elseif ( ! empty( $search ) ) {
			$search_parts = preg_split( '/\s+/', $search );

			if ( is_array( $search_parts ) && 2 === count( $search_parts ) ) {
				$search_meta = array(
					'relation' => 'AND',
					array(
						'key'     => '_give_donor_billing_first_name',
						'value'   => $search_parts[0],
						'compare' => '=',
					),
					array(
						'key'     => '_give_donor_billing_last_name',
						'value'   => $search_parts[1],
						'compare' => '=',
					),
				);
			} else {
				$search_meta = array(
					'relation' => 'OR',
					array(
						'key'     => '_give_donor_billing_first_name',
						'value'   => $search,
						'compare' => 'LIKE',
					),
					array(
						'key'     => '_give_donor_billing_last_name',
						'value'   => $search,
						'compare' => 'LIKE',
					),
				);
			}

			$this->__set( 'meta_query', $search_meta );

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

		$this->__set(
			'meta_query',
			array(
				'key'   => '_give_payment_mode',
				'value' => $this->args['mode'],
			)
		);
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

		$this->__set(
			'meta_query',
			array(
				'key'     => '_give_payment_form_id',
				'value'   => $this->args['give_forms'],
				'compare' => $compare,
			)
		);

		$this->__unset( 'give_forms' );

	}

	/**
	 * Specific Gateway
	 *
	 * @since  1.8.17
	 * @access public
	 *
	 * @return void
	 */
	public function gateway_filter() {

		if ( empty( $this->args['gateway'] ) ) {
			return;
		}

		$compare = '=';

		if ( is_array( $this->args['gateway'] ) ) {
			$compare = 'IN';
		}

		$this->__set(
			'meta_query',
			array(
				'key'     => '_give_payment_gateway',
				'value'   => $this->args['gateway'],
				'compare' => $compare,
			)
		);

		$this->__unset( 'gateway' );

	}


	/**
	 * Get sql query
	 *
	 * Note: Internal purpose only. We are developing on this fn.
	 *
	 * @since  1.8.18
	 * @access public
	 * @global $wpdb
	 *
	 * @return string
	 */
	private function get_sql() {
		global $wpdb;

		$allowed_keys = array(
			'post_name',
			'post_author',
			'post_date',
			'post_title',
			'post_status',
			'post_modified',
			'post_parent',
			'post_type',
			'menu_order',
			'comment_count',
		);

		$this->args['orderby'] = 'post_parent__in';

		// Whitelist orderby.
		if ( ! in_array( $this->args['orderby'], $allowed_keys ) ) {
			$this->args['orderby'] = 'ID';
		}

		$where  = "WHERE {$wpdb->posts}.post_type = 'give_payment'";
		$where .= " AND {$wpdb->posts}.post_status IN ('" . implode( "','", $this->args['post_status'] ) . "')";

		if ( is_numeric( $this->args['post_parent'] ) ) {
			$where .= " AND {$wpdb->posts}.post_parent={$this->args['post_parent']}";
		}

		// Set orderby.
		$orderby  = "ORDER BY {$wpdb->posts}.{$this->args['orderby']}";
		$group_by = '';

		// Set group by.
		if ( ! empty( $this->args['group_by'] ) ) {
			$group_by = "GROUP BY {$wpdb->posts}.{$this->args['group_by']}";
		}

		// Set offset.
		if (
			empty( $this->args['nopaging'] ) &&
			empty( $this->args['offset'] ) &&
			( ! empty( $this->args['page'] ) && 0 < $this->args['page'] )
		) {
			$this->args['offset'] = $this->args['posts_per_page'] * ( $this->args['page'] - 1 );
		}

		// Set fields.
		$fields = "{$wpdb->posts}.*";
		if ( ! empty( $this->args['fields'] ) && 'all' !== $this->args['fields'] ) {
			if ( is_string( $this->args['fields'] ) ) {
				$fields = "{$wpdb->posts}.{$this->args['fields']}";
			} elseif ( is_array( $this->args['fields'] ) ) {
				$fields = "{$wpdb->posts}." . implode( " , {$wpdb->posts}.", $this->args['fields'] );
			}
		}

		// Set count.
		if ( ! empty( $this->args['count'] ) ) {
			$fields = "COUNT({$wpdb->posts}.ID)";

			if ( ! empty( $this->args['group_by'] ) ) {
				$fields = "{$wpdb->posts}.{$this->args['group_by']}, {$fields}";
			}
		}

		// Date query.
		if ( ! empty( $this->args['date_query'] ) ) {
			$date_query_obj = new WP_Date_Query( $this->args['date_query'] );
			$where         .= str_replace(
				array(
					"\n",
					'(   (',
					'))',
				),
				array(
					'',
					'( (',
					') )',
				),
				$date_query_obj->get_sql()
			);
		}

		// Meta query.
		if ( ! empty( $this->args['meta_query'] ) ) {
			$meta_query_obj = new WP_Meta_Query( $this->args['meta_query'] );
			$where          = implode( ' ', $meta_query_obj->get_sql( 'post', $wpdb->posts, 'ID' ) ) . " {$where}";
			$where          = Give()->payment_meta->__rename_meta_table_name( $where, 'posts_where' );
		}

		// Set sql query.
		$sql = $wpdb->prepare(
			"SELECT {$fields} FROM {$wpdb->posts} LIMIT %d,%d;",
			absint( $this->args['offset'] ),
			( empty( $this->args['nopaging'] ) ? absint( $this->args['posts_per_page'] ) : 99999999999 )
		);

		// $where, $orderby and order already prepared query they can generate notice if you re prepare them in above.
		// WordPress consider LIKE condition as placeholder if start with s,f, or d.
		$sql = str_replace( 'LIMIT', "{$where} {$group_by} {$orderby} {$this->args['order']} LIMIT", $sql );

		return $sql;
	}

	/**
	 * Update donations meta cache
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @param $donation_ids
	 */
	public static function update_meta_cache( $donation_ids ) {
		// Exit.
		if ( empty( $donation_ids ) ) {
			return;
		}

		update_meta_cache( Give()->payment_meta->get_meta_type(), $donation_ids );
	}
}

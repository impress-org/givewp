<?php
/**
 * Donor Wall
 *
 * @package     Give
 * @subpackage  Classes/Give_Donor_Wall
 * @copyright   Copyright (c) 2020, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


/**
 * Give_Donor_Wall Class
 *
 * This class handles donors.
 *
 * @since 2.2.0
 */
class Give_Donor_Wall {

	/**
	 * Instance.
	 *
	 * @since  2.2.0
	 * @access private
	 * @var Give_Donor_Wall
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.2.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.2.0
	 * @access public
	 * @return Give_Donor_Wall
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since  2.2.0
	 *
	 * @return void
	 */
	public function setup_actions() {

		add_shortcode( 'give_donor_wall', [ $this, 'render_shortcode' ] );

		add_action( 'wp_ajax_give_get_donor_comments', [ $this, 'ajax_handler' ] );
		add_action( 'wp_ajax_nopriv_give_get_donor_comments', [ $this, 'ajax_handler' ] );

	}


	/**
	 * Displays donors in a grid layout.
	 *
	 * @since  2.2.0
	 *
	 * @param array $atts                {
	 *                                   Optional. Attributes of the donor wall shortcode.
	 *
	 * @type int    $donors_per_page     Number of donors per page. Default '20'.
	 * @type int    $form_id             The donation form to filter donors by. Default is all forms (no filter).
	 * @type bool   $paged               Whether to paginate donors. Default 'true'.
	 * @type string $ids                 A comma-separated list of donor IDs to display. Default empty.
	 * @type string $columns             Maximum columns to display. Default 'best-fit'.
	 *                                   Accepts 'best-fit', '1', '2', '3', '4'.
	 * @type bool   $show_avatar         Whether to display the donor's gravatar image if available. Default 'true'.
	 * @type bool   $show_name           Whether to display the donor's full name, first and last. Default 'true'.
	 * @type bool   $show_company_name   Whether to display the donor's company name. Default 'false'.
	 * @type bool   $show_total          Whether to display the donor's donation amount. Default 'true'.
	 * @type bool   $show_time           Whether to display date of the last donation. Default 'true'.
	 * @type bool   $show_comments       Whether to display the donor's comment if they left one. Default 'true'.
	 * @type int    $comment_length      The number of words to display for the comments before a "Read more" field
	 * @type int    $only_comments       Whether to display the donors only with comment. Default 'false'.
	 *
	 * @type string $readmore_text       Link label for modal in which donor can read full comment.
	 * @type string $loadmore_text       Button label which will load more donor comments.
	 * @type int    $avatar_size         Avatar image size in pixels without the "px". Default "60"
	 * @type string $orderby             The order in which you want the donations to appear.
	 *                                   Currently we are using this attribute internally and it will sort donations by created date.
	 * @type string $order               The order in which you want the donors to appear. Accepts "ASC". "DESC".
	 *
	 * }
	 * @return string|bool The markup of the form grid or false.
	 */
	public function render_shortcode( $atts ) {

		$give_settings = give_get_settings();

		$atts      = $this->parse_atts( $atts );
		$donations = $this->get_donation_data( $atts );
		$html      = '';

		if ( $donations ) {

			ob_start();

			foreach ( $donations as $donation ) {
				// Give/templates/shortcode-donor-wall.php.
				give_get_template( 'shortcode-donor-wall', [ $donation, $give_settings, $atts ] );
			}

			$html = ob_get_clean();

			// Return only donor html.
			if (
				isset( $atts['only_donor_html'] )
				&& wp_doing_ajax()
				&& $atts['only_donor_html']
			) {
				return $html;
			}
		}

		$temp_atts          = $atts;
		$temp_atts['paged'] = $atts['paged'] + 1;

		$more_btn_html = sprintf(
			'<input type="hidden" class="give-donor-wall-shortcode-attrs" data-shortcode="%1$s">',
			rawurlencode( http_build_query( $atts ) )
		);

		if ( $this->has_donations( $temp_atts ) ) {
			$more_btn_html .= sprintf(
				'<button class="give-donor__load_more give-button-with-loader"><span class="give-loading-animation"></span>%1$s</button>',
				$atts['loadmore_text']
			);
		}

		$html = $html
			? sprintf(
				'<div class="give-wrap give-grid-ie-utility"><div class="give-grid give-grid--%1$s">%2$s</div>%3$s</div>',
				esc_attr( $atts['columns'] ),
				$html,
				$more_btn_html
			)
			: '';

		return $html;
	}

	/**
	 * Parse shortcode attributes
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return array
	 */
	public function parse_atts( $atts ) {
		$atts = shortcode_atts(
			[
				'donors_per_page'   => 12,
				'form_id'           => 0,
				'paged'             => 1,
				'ids'               => '',
				'columns'           => 'best-fit',
				'anonymous'         => true,
				'show_avatar'       => true,
				'show_name'         => true,
				'show_company_name' => false,
				'show_total'        => true,
				'show_time'         => true,
				'show_comments'     => true,
				'comment_length'    => 140,
				'only_comments'     => false,
				'readmore_text'     => esc_html__( 'Read more', 'give' ),
				'loadmore_text'     => esc_html__( 'Load more', 'give' ),
				'avatar_size'       => 60,
				'orderby'           => 'post_date',
				'order'             => 'DESC',
				'hide_empty'        => true,  // Deprecated in 2.3.0
				'only_donor_html'   => false, // Only for internal use.
			],
			$atts
		);

		// Validate boolean attributes.
		$boolean_attributes = [
			'anonymous',
			'show_avatar',
			'show_name',
			'show_company_name',
			'show_total',
			'show_time',
			'show_comments',
			'show_comments',
			'hide_empty',
			'only_comments',
			'only_donor_html',
		];

		foreach ( $boolean_attributes as $att ) {
			// Convert numeric to boolean.
			// It will prevent condition check against boolean value.
			if ( is_numeric( $atts[ $att ] ) ) {
				$atts[ $att ] = (bool) $atts[ $att ];
			}

			$atts[ $att ] = filter_var( $atts[ $att ], FILTER_VALIDATE_BOOLEAN );
		}

		// Validate numeric attributes.
		$numeric_attributes = [
			'donors_per_page',
			'form_id',
			'paged',
			'comment_length',
			'avatar_size',
		];

		foreach ( $numeric_attributes as $att ) {
			// It will prevent condition check against numeric value.
			$atts[ $att ] = absint( $atts[ $att ] );
		}

		// Validate comma separated numeric attributes and keep original data format ( comma separated string).
		if ( ! empty( $atts['ids'] ) ) {
			if ( false === strpos( $atts['ids'], ',' ) ) {
				$tmp = [ absint( $atts['ids'] ) ];
			} else {
				$tmp = array_filter(
					array_map(
						static function( $id ) {
							return absint( trim( $id ) ); },
						explode( ',', $atts['ids'] )
					)
				);
			}

			$atts['ids'] = implode( ',', $tmp );
		}

		return $atts;
	}

	/**
	 * Get donors
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $donor_query Donor query.
	 *
	 * @return array
	 */
	public function get_donors( $donor_query ) {
		$donor_query = new Give_Donors_Query( $donor_query );

		return $donor_query->get_donors();
	}


	/**
	 * Ajax handler
	 *
	 * @since  2.2.0
	 * @access public
	 */
	public function ajax_handler() {
		$shortcode_atts = array_map( 'give_clean', wp_parse_args( rawurldecode( $_POST['data'] ) ) ); // @codingStandardsIgnoreLine

		// Get next page donor comments.
		$shortcode_atts['paged']           = $shortcode_atts['paged'] + 1;
		$shortcode_atts['only_donor_html'] = true;

		$donors_comment_html = $this->render_shortcode( $shortcode_atts );

		// Check if donor comment remaining.
		$temp_atts          = $shortcode_atts;
		$temp_atts['paged'] = $shortcode_atts['paged'] + 1;
		$has_donors         = $this->has_donations( $temp_atts ) ? 1 : 0;

		// Remove internal shortcode param.
		unset( $shortcode_atts['only_donor_html'] );

		wp_send_json(
			[
				'shortcode' => rawurlencode( http_build_query( $shortcode_atts ) ),
				'html'      => $donors_comment_html,
				'remaining' => $has_donors,
			]
		);
	}

	/**
	 * Get query params
	 *
	 * @since 2.3.0
	 *
	 * @param  array $atts
	 *
	 * @return array
	 */
	private function get_query_param( $atts = [] ) {
		$valid_order   = [ 'ASC', 'DESC' ];
		$valid_orderby = [ 'post_date', 'donation_amount' ];

		$query_atts = [];

		$query_atts['order']         = in_array( $atts['order'], $valid_order ) ? $atts['order'] : 'DESC';
		$query_atts['orderby']       = in_array( $atts['orderby'], $valid_orderby ) ? $atts['orderby'] : 'post_date';
		$query_atts['limit']         = $atts['donors_per_page'];
		$query_atts['offset']        = $atts['donors_per_page'] * ( $atts['paged'] - 1 );
		$query_atts['form_id']       = $atts['form_id'];
		$query_atts['ids']           = implode( '\',\'', explode( ',', $atts['ids'] ) );
		$query_atts['only_comments'] = ( true === $atts['only_comments'] );
		$query_atts['anonymous']     = ( true === $atts['anonymous'] );

		return $query_atts;
	}

	/**
	 * Get donation data.
	 *
	 * @since 2.3.0
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	private function get_donation_data( $atts = [] ) {
		global $wpdb;

		// Bailout if donation does not exist.
		if ( ! ( $donation_ids = $this->get_donations( $atts ) ) ) {
			return [];
		}

		$donation_ids = ! empty( $donation_ids )
			? '\'' . implode( '\',\'', $donation_ids ) . '\''
			: '';

		// Backward compatibility
		$donation_id_col = Give()->payment_meta->get_meta_type() . '_id';

		$sql = "SELECT m1.*, p1.post_date as donation_date FROM {$wpdb->donationmeta} as m1
				INNER JOIN {$wpdb->posts} as p1 ON (m1.{$donation_id_col}=p1.ID)
				WHERE m1.{$donation_id_col} IN ( {$donation_ids} )
				ORDER BY FIELD( p1.ID, {$donation_ids} )
				";

		$results = (array) $wpdb->get_results( $sql );

		if ( ! empty( $results ) ) {
			$temp = [];

			/* @var stdClass $result */
			foreach ( $results as $result ) {
				$temp[ $result->{$donation_id_col} ][ $result->meta_key ] = maybe_unserialize( $result->meta_value );

				// Set donation date.
				if ( empty( $temp[ $result->{$donation_id_col} ]['donation_date'] ) ) {
					$temp[ $result->{$donation_id_col} ]['donation_date'] = $result->donation_date;
				}
			}

			$comments = $this->get_donor_comments( $temp );

			if ( ! empty( $temp ) ) {
				foreach ( $temp as $donation_id => $donation_data ) {
					$temp[ $donation_id ]['donation_id'] = $donation_id;

					$temp[ $donation_id ]['name_initial'] = give_get_name_initial(
						[
							'firstname' => $donation_data['_give_donor_billing_first_name'],
							'lastname'  => $donation_data['_give_donor_billing_last_name'],
						]
					);

					$temp[ $donation_id ]['donor_comment'] = ! empty( $comments[ $donation_id ] ) ? $comments[ $donation_id ] : '';
				}
			}

			$results = ! empty( $temp ) ? $temp : [];
		}

		return $results;
	}

	/**
	 * Get donation list for specific query
	 *
	 * @since 2.3.0
	 *
	 * @param  array $atts
	 *
	 * @return array
	 */
	private function get_donations( $atts = [] ) {
		global $wpdb;

		// Backward compatibility
		$donation_id_col = Give()->payment_meta->get_meta_type() . '_id';

		$query_params = $this->get_query_param( $atts );

		$sql   = "SELECT p1.ID FROM {$wpdb->posts} as p1";
		$where = " WHERE p1.post_status IN ('publish') AND p1.post_type = 'give_payment'";

		// exclude donation with zero amount from result.
		$sql   .= " INNER JOIN {$wpdb->donationmeta} as m1 ON (p1.ID = m1.{$donation_id_col})";
		$where .= " AND m1.meta_key='_give_payment_total' AND m1.meta_value>0";

		if ( $query_params['form_id'] ) {
			$sql   .= " INNER JOIN {$wpdb->donationmeta} as m2 ON (p1.ID = m2.{$donation_id_col})";
			$where .= " AND m2.meta_key='_give_payment_form_id' AND m2.meta_value={$query_params['form_id']}";
		}

		// Get donations only from specific donors.
		if ( $query_params['ids'] ) {
			$sql   .= " INNER JOIN {$wpdb->donationmeta} as m3 ON (p1.ID = m3.{$donation_id_col})";
			$where .= " AND m3.meta_key='_give_payment_donor_id' AND m3.meta_value IN ('{$query_params['ids']}')";
		}

		// exclude donations which does not has donor comment.
		if ( $query_params['only_comments'] ) {
			$sql   .= " INNER JOIN {$wpdb->give_comments} as gc1 ON (p1.ID = gc1.comment_parent)";
			$where .= " AND gc1.comment_type='donor_donation'";
		}

		// exclude anonymous donation form query based on query parameters.
		if (
			! $query_params['anonymous']
			|| $query_params['only_comments']
		) {
			$where .= " AND p1.ID NOT IN ( SELECT DISTINCT({$donation_id_col}) FROM {$wpdb->donationmeta} WHERE meta_key='_give_anonymous_donation' AND meta_value='1')";
		}

		// order by query based on parameter.
		if ( 'donation_amount' === $query_params['orderby'] ) {
			$order = " ORDER BY m1.meta_value+0 {$query_params['order']}";
		} else {
			$order = " ORDER BY p1.{$query_params['orderby']} {$query_params['order']}, p1.ID {$query_params['order']}";
		}

		$limit  = " LIMIT {$query_params['limit']}";
		$offset = " OFFSET {$query_params['offset']}";

		$sql .= $where . $order . $limit . $offset;

		return $wpdb->get_col( $sql );
	}

	/**
	 * Get donor comments
	 *
	 * @since 2.3.0
	 *
	 * @param array $donations_data
	 *
	 * @return array
	 */
	private function get_donor_comments( $donations_data = [] ) {
		global $wpdb;
		$comments = [];

		// Bailout.
		if ( empty( $donations_data ) ) {
			return $comments;
		}

		// Backward compatibility.
		if (
			! give_has_upgrade_completed( 'v230_move_donor_note' )
			|| ! give_has_upgrade_completed( 'v230_move_donation_note' )
		) {
			foreach ( $donations_data as $id => $data ) {
				$comment         = give_get_donor_donation_comment( $id, $data['_give_payment_donor_id'] );
				$comments[ $id ] = ! empty( $comment ) ? $comment->comment_content : '';
			}

			return $comments;
		}

		$sql   = "SELECT c1.comment_parent as donation_id, c1.comment_content as comment FROM {$wpdb->give_comments} as c1";
		$sql  .= " INNER JOIN {$wpdb->give_commentmeta} as cm1 ON (c1.comment_ID=cm1.give_comment_id)";
		$where = [];

		foreach ( $donations_data as $id => $data ) {
			// Do not fetch comment for anonymous donation.
			if ( ! empty( $data['_give_anonymous_donation'] ) ) {
				continue;
			}

			$where[] = "(c1.comment_parent={$id} AND cm1.meta_key='_give_donor_id' AND cm1.meta_value={$data['_give_payment_donor_id']})";
		}

		$where  = ' WHERE ' . implode( ' OR ', $where );
		$where .= " AND c1.comment_type='donor_donation'";

		$sql = $sql . $where;

		$comments = (array) $wpdb->get_results( $sql );

		if ( ! empty( $comments ) ) {
			$comments = array_combine(
				wp_list_pluck( $comments, 'donation_id' ),
				wp_list_pluck( $comments, 'comment' )
			);
		}

		return $comments;
	}

	/**
	 * Check if donation exist or not for specific query
	 *
	 * @since 2.3.0
	 *
	 * @param  array $atts
	 *
	 * @return bool
	 */
	private function has_donations( $atts = [] ) {
		return (bool) $this->get_donations( $atts );
	}
}

// Initialize shortcode.
Give_Donor_Wall::get_instance();

<?php
/**
 * Donors Gravatars
 *
 * @package     Give
 * @subpackage  Classes/Give_Donors_Gravatars
 * @copyright   Copyright (c) 2016, WordImpress
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
	static private $instance;

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

		add_shortcode( 'give_donor_wall', array( $this, 'render_shortcode' ) );

		add_action( 'wp_ajax_give_get_donor_comments', array( $this, 'ajax_handler' ) );
		add_action( 'wp_ajax_nopriv_give_get_donor_comments', array( $this, 'ajax_handler' ) );

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
	 * @type bool   $show_total          Whether to display the donor's donation amount. Default 'true'.
	 * @type bool   $show_time           Whether to display date of the last donation. Default 'true'.
	 * @type bool   $show_comments       Whether to display the donor's comment if they left one. Default 'true'.
	 * @type int    $comment_length      The number of words to display for the comments before a "Read more" field
	 *                                   displays. Default '20'.
	 * @type string $readmore_text       Link label for modal in which donor can read full comment.
	 * @type string $loadmore_text       Button label which will load more donor comments.
	 * @type int    $avatar_size         Avatar image size in pixels without the "px". Default "60"
	 * @type string $orderby             The order in which you want the donors to appear. Accepts "donation_amount", "donation_count",
	 *                                   if donor donated same value for orderby attribute then they will short by created date (fallback)
	 * @type string $order               The order in which you want the donors to appear. Accepts "ASC". "DESC".
	 *
	 * }
	 * @return string|bool The markup of the form grid or false.
	 */
	public function render_shortcode( $atts ) {

		$give_settings = give_get_settings();

		$atts      = $this->parse_atts( $atts );
		$donations = $this->get_sql( $atts );
		$html      = '';

		if ( $donations ) {

			ob_start();

			foreach ( $donations as $donation ) {
				// Give/templates/shortcode-donor-wall.php.
				give_get_template( 'shortcode-donor-wall', array( $donation, $give_settings, $atts ) );
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

		$more_btn_html = '';
		if ( $this->has_donations( $temp_atts ) ) {
			$more_btn_html = sprintf(
				'<button class="give-donor__load_more give-button-with-loader" data-shortcode="%1$s"><span class="give-loading-animation"></span>%2$s</button>',
				rawurlencode( http_build_query( $atts ) ),
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
			array(
				'number'          => 20,
				'form_id'         => 0,
				'paged'           => 1,
				'ids'             => '',
				'columns'         => 'best-fit',
				'show_avatar'     => true,
				'show_name'       => true,
				'show_total'      => true,
				'show_time'       => true,
				'show_comments'   => true,
				'comment_length'  => 140,
				'only_comments'   => false,
				'readmore_text'   => esc_html__( 'Read More', 'give' ),
				'loadmore_text'   => esc_html__( 'Load More', 'give' ),
				'avatar_size'     => 60,
				'orderby'         => 'post_date',
				'order'           => 'DESC',
				'hide_empty'      => true,
				'only_donor_html' => false, // Only for internal use.
			), $atts
		);


		// Validate boolean attributes.
		$boolean_attributes = array(
			'show_avatar',
			'show_name',
			'show_total',
			'show_time',
			'show_comments',
			'show_comments',
			'hide_empty',
			'only_comments',
			'only_donor_html',
		);

		foreach ( $boolean_attributes as $att ) {
			// Convert numeric to boolean.
			// It will prevent condition check against boolean value.
			if ( is_numeric( $atts[ $att ] ) ) {
				$atts[ $att ] = (bool) $atts[ $att ];
			}

			$atts[ $att ] = filter_var( $atts[ $att ], FILTER_VALIDATE_BOOLEAN );
		}

		return $atts;
	}

	/**
	 * Get donors
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $donor_query Dorno query.
	 *
	 * @return array
	 */
	public function get_donors( $donor_query ) {
		$donor_query = new Give_Donors_Query( $donor_query );
		$donors      = $donor_query->get_donors();

		return $donors;
	}


	/**
	 * Ajax handler
	 *
	 * @since  2.2.0
	 * @access public
	 */
	public function ajax_handler() {
		$shortcode_atts = wp_parse_args( give_clean( rawurldecode( $_POST['data'] ) ) ); // @codingStandardsIgnoreLine

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
			array(
				'shortcode' => rawurlencode( http_build_query( $shortcode_atts ) ),
				'html'      => $donors_comment_html,
				'remaining' => $has_donors,
			)
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
	private function get_query_param( $atts = array() ) {
		$valid_order   = array( 'ASC', 'DESC' );
		$valid_orderby = array( 'post_date' );

		$query_atts = array();

		$query_atts['order']   = in_array( $atts['order'], $valid_order ) ? $atts['order'] : 'DESC';
		$query_atts['orderby'] = in_array( $atts['orderby'], $valid_orderby ) ? $atts['orderby'] : 'post_date';
		$query_atts['limit']   = $atts['number'];
		$query_atts['offset']  = $atts['number'] * ( $atts['paged'] - 1 );
		$query_atts['form_id'] = $atts['form_id'];

		return $query_atts;
	}

	/**
	 * Get results by direct sql querys.
	 *
	 * @since 2.3.0
	 *
	 * @param array $atts
	 *
	 * @return array
	 */
	private function get_sql( $atts = array() ) {
		global $wpdb;

		$query_params = $this->get_query_param( $atts );

		// Bailout if donation does not exist.
		if ( ! ( $donation_ids = $this->get_donations( $atts ) ) ) {
			return array();
		}

		$donation_ids = ! empty( $donation_ids )
			? '\'' . implode( '\',\'', $donation_ids ) . '\''
			: '';


		$sql = "SELECT * FROM {$wpdb->donationmeta}
				WHERE donation_id IN ( {$donation_ids} )
				ORDER BY donation_id {$query_params['order']}
				";

		$results = (array) $wpdb->get_results( $sql );

		if ( ! empty( $results ) ) {
			$temp = array();

			/* @var stdClass $result */
			foreach ( $results as $result ) {
				$temp[ $result->donation_id ][ $result->meta_key ] = maybe_unserialize( $result->meta_value );
			}

			if ( ! empty( $temp ) ) {
				foreach ( $temp as $donation_id => $donation_data ) {
					$temp[ $donation_id ]['donation_id'] = $donation_id;

					$temp[ $donation_id ]['name_initial'] = give_get_name_initial( array(
						'firstname' => $donation_data['_give_donor_billing_first_name'],
						'lastname'  => $donation_data['_give_donor_billing_first_name'],
					) );

					$temp[ $donation_id ]['donor_comment'] = give_get_donor_donation_comment( $donation_id, $donation_data['_give_payment_donor_id'] );
				}
			}

			$results = ! empty( $temp ) ? $temp : array();
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
	private function get_donations( $atts = array() ) {
		global $wpdb;

		$query_params = $this->get_query_param( $atts );

		$sql = "
			SELECT ID FROM {$wpdb->posts}
			WHERE post_status IN ('publish')
			AND post_type = 'give_payment'
			ORDER BY {$query_params['orderby']} {$query_params['order']}
			LIMIT {$query_params['limit']}
			OFFSET {$query_params['offset']}
			";

		if( $query_params['form_id'] ) {
			$sql = "
			SELECT p1.ID FROM {$wpdb->posts} as p1
			INNER JOIN {$wpdb->donationmeta} as m1 ON (p1.ID = m1.donation_id)
			WHERE p1.post_status IN ('publish')
			AND p1.post_type = 'give_payment'
			AND m1.meta_key='_give_payment_form_id'
			AND m1.meta_value={$query_params['form_id']}
			ORDER BY {$query_params['orderby']} {$query_params['order']}
			LIMIT {$query_params['limit']}
			OFFSET {$query_params['offset']}
			";
		}

		$donation_ids = $wpdb->get_col( $sql );

		return $donation_ids;
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
	private function has_donations( $atts = array() ) {
		return (bool) $this->get_donations( $atts );
	}
}

// Initialize shortcode.
Give_Donor_Wall::get_instance();

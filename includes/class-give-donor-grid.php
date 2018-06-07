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
 * @since 2.1
 */
class Give_Donor_Wall {

	/**
	 * Class Constructor
	 *
	 * Set up the Give Donors Gravatars Class.
	 *
	 * @since  2.1
	 * @access public
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since  2.1
	 *
	 * @return void
	 */
	public function setup_actions() {

		add_shortcode( 'give_donor_grid', array( $this, 'donor_grid_shortcode' ) );

	}


	/**
	 * Displays donors in a grid layout.
	 *
	 * @since  2.1.0
	 *
	 * @param array $atts                {
	 *                                   Optional. Attributes of the form grid shortcode.
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
	 *       displays. Default '20'.
	 * @type int    $avatar_size         Avatar image size in pixels without the "px". Default "60"
	 * @type string $orderby             The order in which you want the donors to appear. Accepts "donation_amount",
	 *       "donation_count", "". Default "donation_count".
	 * }
	 * @return string|bool The markup of the form grid or false.
	 */
	public function donor_grid_shortcode( $atts ) {

		$give_settings = give_get_settings();

		$atts = shortcode_atts(
			array(
				'donors_per_page' => 20,
				'form_id'         => 0,
				'paged'           => true,
				'ids'             => '',
				'columns'         => 'best-fit',
				'show_avatar'     => true,
				'show_name'       => true,
				'show_total'      => true,
				'show_time'       => true,
				'show_comments'   => true,
				'comment_length'  => 20,
				'readmore_text'   => esc_html__( 'Read More', 'give' ),
				'avatar_size'     => 60,
				'orderby'         => 'donation_count',
				'order'           => 'DESC',
				'hide_empty'      => true,
			), $atts
		);

		// Validate integer attributes.
		$atts['donors_per_page'] = intval( $atts['donors_per_page'] );

		// Validate boolean attributes.
		$boolean_attributes = array(
			'paged',
			'show_avatar',
			'show_name',
			'show_total',
			'show_time',
			'hide_empty',
		);

		foreach ( $boolean_attributes as $att ) {
			$atts[ $att ] = filter_var( $atts[ $att ], FILTER_VALIDATE_BOOLEAN );
		}

		$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;

		// Set default form query args.
		$donor_args = array(
			'number'  => $atts['donors_per_page'],
			'offset'  => $atts['donors_per_page'] * ( $paged - 1 ),
			'orderby' => $atts['orderby'],
			'order'   => $atts['order'],
		);

		// Hide donors with zero donation amount.
		if ( $atts['hide_empty'] ) {
			$donor_args['donation_amount'] = array(
				'compare' => '>=',
				'amount'  => 1,
			);
		}

		// Show donor who donated to specific form.
		if ( $atts['form_id'] ) {
			$donor_args['give_forms'] = $atts['form_id'];
		}

		// Replace donation with purchase because donor table has that prefix in column name.
		$donor_args['orderby'] = str_replace( array( 'donation', 'amount' ), array( 'purchase', 'value' ), $atts['orderby'] );

		// Query to output donation forms.
		$donor_query = new Give_Donors_Query( $donor_args );
		$donors      = $donor_query->get_donors();

		if ( $donors ) {

			ob_start();

			echo '<div class="give-wrap">';
			echo '<div class="give-grid give-grid--' . esc_attr( $atts['columns'] ) . '">';

			foreach ( $donors as $donor ) {
				// Give/templates/shortcode-donor-grid.php.
				give_get_template( 'shortcode-donor-grid', array( $donor, $give_settings, $atts ) );
			}

			echo '</div><!-- .give-grid -->';

			if ( false !== $atts['paged'] ) {

				$_donor_query['number'] = - 1;
				$_donor_query['offset'] = 0;
				$donor_count            = count( Give()->donors->get_donors( $_donor_query ) );

				$paginate_args = array(
					'current'   => max( 1, get_query_var( 'paged' ) ),
					'total'     => ceil( $donor_count / $atts['donors_per_page'] ),
					'show_all'  => false,
					'end_size'  => 1,
					'mid_size'  => 2,
					'prev_next' => true,
					'prev_text' => __( '« Previous', 'give' ),
					'next_text' => __( 'Next »', 'give' ),
					'type'      => 'plain',
					'add_args'  => false,
				);

				printf( '<div class="give-page-numbers">%s</div>', paginate_links( $paginate_args ) );
				echo '</div><!-- .give-wrap -->';
			}

			return ob_get_clean();
		}
	}


}


new Give_Donor_Wall();

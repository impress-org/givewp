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

		//		add_shortcode( 'give_donors_gravatars', array( $this, 'shortcode' ) );
		//		add_filter( 'give_settings_display', array( $this, 'settings' ) );
		//		do_action( 'give_donors_gravatars_setup_actions' );
	}


	/**
	 * Displays donors in a grid layout.
	 *
	 * @since  2.1.0
	 *
	 * @param array $atts                {
	 *                                   Optional. Attributes of the form grid shortcode.
	 *
	 * @type int    $forms_per_page      Number of forms per page. Default '12'.
	 * @type bool   $paged               Whether to paginate forms. Default 'true'.
	 * @type string $ids                 A comma-separated list of donor IDs to display. Default empty.
	 * @type string $columns             Maximum columns to display. Default 'best-fit'.
	 *                                   Accepts 'best-fit', '1', '2', '3', '4'.
	 * @type bool   $show_title          Whether to display form title. Default 'true'.
	 * @type bool   $show_goal           Whether to display form goal. Default 'true'.
	 * @type string $avatar_size         Avatar image size in pixels without the "px". Default "
	 * }
	 * @return string|bool The markup of the form grid or false.
	 */
	public function donor_grid_shortcode( $atts ) {

		$give_settings = give_get_settings();

		$atts = shortcode_atts( array(
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
			'avatar_size'     => 60,
			'orderby'         => 'purchase_count',
			'order'           => 'DESC',
			'hide_empty'      => true
		), $atts );

		// Validate integer attributes.
		$atts['donors_per_page'] = intval( $atts['donors_per_page'] );

		// Validate boolean attributes.
		$boolean_attributes = array(
			'paged',
			'show_avatar',
			'show_name',
			'show_total',
			'show_time',
			'hide_empty'
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
			'order'   => $atts['order']
		);

		// Hide donors with zero donation amount.
		if( $atts['hide_empty'] ) {
			$donor_args['donation_amount'] = array(
				'compare' => '>=',
				'amount'  => 1
			);
		}

		// Hide donors with zero donation amount.
		if( $atts['form_id'] ) {
			$donor_args['give_forms'] = $atts['form_id'];
		}

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


	/**
	 * Utility function to check if a gravatar exists for a given email or id
	 *
	 * @see    : https://gist.github.com/justinph/5197810
	 *
	 * @since  2.1
	 * @access public
	 *
	 * @param  int|string|object $id_or_email A user ID, email address, or comment object
	 *
	 * @return bool If the gravatar exists or not
	 */
	public function validate_gravatar( $id_or_email ) {
		// id or email code borrowed from wp-includes/pluggable.php
		$email = '';
		if ( is_numeric( $id_or_email ) ) {
			$id   = (int) $id_or_email;
			$user = get_userdata( $id );
			if ( $user ) {
				$email = $user->user_email;
			}
		} elseif ( is_object( $id_or_email ) ) {
			// No avatar for pingbacks or trackbacks
			$allowed_comment_types = apply_filters( 'get_avatar_comment_types', array( 'comment' ) );
			if ( ! empty( $id_or_email->comment_type ) && ! in_array( $id_or_email->comment_type, (array) $allowed_comment_types ) ) {
				return false;
			}

			if ( ! empty( $id_or_email->user_id ) ) {
				$id   = (int) $id_or_email->user_id;
				$user = get_userdata( $id );
				if ( $user ) {
					$email = $user->user_email;
				}
			} elseif ( ! empty( $id_or_email->comment_author_email ) ) {
				$email = $id_or_email->comment_author_email;
			}
		} else {
			$email = $id_or_email;
		}

		$hashkey = md5( strtolower( trim( $email ) ) );
		$uri     = 'http://www.gravatar.com/avatar/' . $hashkey . '?d=404';

		$data = Give_Cache::get_group( $hashkey );

		if ( is_null( $data ) ) {
			$response = wp_remote_head( $uri );
			if ( is_wp_error( $response ) ) {
				$data = 'not200';
			} else {
				$data = $response['response']['code'];
			}
			Give_Cache::set_group( $hashkey, $data, $group = '', $expire = 60 * 5 );

		}
		if ( $data == '200' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Settings
	 *
	 * @since  2.1
	 * @access public
	 *
	 * @param  array $settings Gravatar settings.
	 *
	 * @return array           Gravatar settings.
	 */
	public function settings( $settings ) {

		$give_gravatar_settings = array(
			array(
				'name' => __( 'Donator Gravatars', 'give' ),
				'desc' => '<hr>',
				'id'   => 'give_title',
				'type' => 'give_title'
			),
			array(
				'name'    => __( 'Gravatar Size', 'give' ),
				'desc'    => __( 'The size of each Gravatar in pixels (512px maximum).', 'give' ),
				'type'    => 'text_small',
				'id'      => 'give_donors_gravatars_gravatar_size',
				'default' => '64'
			),
			array(
				'name' => __( 'Minimum Unique Donations Required', 'give' ),
				'desc' => __( 'The minimum number of unique donations a form must have before the Gravatars are shown. Leave blank for no minimum.', 'give' ),
				'type' => 'text_small',
				'id'   => 'give_donors_gravatars_min_purchases_required',
			),
			array(
				'name'    => __( 'Maximum Gravatars To Show', 'give' ),
				'desc'    => __( 'The maximum number of gravatars to show. Leave blank for no limit.', 'give' ),
				'type'    => 'text',
				'id'      => 'give_donors_gravatars_maximum_number',
				'default' => '20',
			),
			array(
				'name' => __( 'Gravatar Visibility', 'give' ),
				'desc' => __( 'Show only donors with a Gravatar account.', 'give' ),
				'id'   => 'give_donors_gravatars_has_gravatar_account',
				'type' => 'checkbox',
			),
			array(
				'name' => __( 'Randomize Gravatars', 'give' ),
				'desc' => __( 'Randomize the Gravatars.', 'give' ),
				'id'   => 'give_donors_gravatars_random_gravatars',
				'type' => 'checkbox',
			),
		);

		return array_merge( $settings, $give_gravatar_settings );
	}

}


new Give_Donor_Wall();

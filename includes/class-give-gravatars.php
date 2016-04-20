<?php
/**
 * Donators Gravatars
 *
 * @package     Give
 * @subpackage  Classes/Donators
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Donators_Gravatars {

	/**
	 * Start your engines
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since 1.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		//		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		//		add_shortcode( 'give_donators_gravatars', array( $this, 'shortcode' ) );
		//		add_filter( 'give_settings_display', array( $this, 'settings' ) );
		//		do_action( 'give_donators_gravatars_setup_actions' );
	}

	/**
	 * Utility function to check if a gravatar exists for a given email or id
	 *
	 * @param int|string|object $id_or_email A user ID, email address, or comment object
	 *
	 * @return bool if the gravatar exists or not
	 */

	// https://gist.github.com/justinph/5197810
	function validate_gravatar( $id_or_email ) {
		//id or email code borrowed from wp-includes/pluggable.php
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

		$data = wp_cache_get( $hashkey );
		if ( false === $data ) {
			$response = wp_remote_head( $uri );
			if ( is_wp_error( $response ) ) {
				$data = 'not200';
			} else {
				$data = $response['response']['code'];
			}
			wp_cache_set( $hashkey, $data, $group = '', $expire = 60 * 5 );

		}
		if ( $data == '200' ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get an array of all the log IDs using the Give Logging Class
	 *
	 * @since 1.0
	 * @return array if logs, false otherwise
	 *
	 * @param int $form_id
	 */
	function get_log_ids( $form_id = '' ) {

		// get Give_Logging class
		global $give_logs;

		// get log for this form
		$logs = $give_logs->get_logs( $form_id );

		if ( $logs ) {
			// make an array with all the donor IDs
			foreach ( $logs as $log ) {
				$log_ids[] = $log->ID;
			}

			return $log_ids;
		}

		return null;

	}


	/**
	 * Get payment ID
	 *
	 * @since 1.0
	 */
	function get_payment_ids( $form_id = '' ) {

		global $give_options;

		$log_ids = $this->get_log_ids( $form_id );

		if ( $log_ids ) {

			$payment_ids = array();

			foreach ( $log_ids as $id ) {
				// get the payment ID for each corresponding log ID
				$payment_ids[] = get_post_meta( $id, '_give_log_payment_id', true );
			}

			// remove donors who have purchased more than once so we can have unique avatars
			$unique_emails = array();

			foreach ( $payment_ids as $key => $id ) {

				$email = get_post_meta( $id, '_give_payment_user_email', true );

				if ( isset ( $give_options['give_donators_gravatars_has_gravatar_account'] ) ) {
					if ( ! $this->validate_gravatar( $email ) ) {
						continue;
					}
				}

				$unique_emails[ $id ] = get_post_meta( $id, '_give_payment_user_email', true );

			}

			// strip duplicate emails
			$unique_emails = array_unique( $unique_emails );

			// convert the unique IDs back into simple array
			foreach ( $unique_emails as $id => $email ) {
				$unique_ids[] = $id;
			}

			// randomize the payment IDs if enabled
			if ( isset( $give_options['give_donators_gravatars_random_gravatars'] ) ) {
				shuffle( $unique_ids );
			}

			// return our unique IDs
			return $unique_ids;

		}

	}


	/**
	 * Gravatars
	 *
	 * @since 1.0
	 */
	function gravatars( $form_id = false, $title = '' ) {

		// unique $payment_ids 
		$payment_ids = $this->get_payment_ids( $form_id );

		//			var_dump( $payment_ids );
		//			 var_dump( $this->get_log_ids( get_the_ID() ) );

		global $give_options;

		// return if no ID
		if ( ! $form_id ) {
			return;
		}

		// minimum amount of purchases before showing gravatars
		// if the number of items in array is not greater or equal to the number specified, then exit
		if ( isset( $give_options['give_donators_gravatars_min_purchases_required'] ) && '' != $give_options['give_donators_gravatars_min_purchases_required'] ) {
			if ( ! ( count( $payment_ids ) >= $give_options['give_donators_gravatars_min_purchases_required'] ) ) {
				return;
			}
		}

		ob_start();

		$output = '';
		echo '<div id="give-purchase-gravatars">';


		if ( isset ( $title ) ) {

			if ( $title ) {
				echo apply_filters( 'give_donators_gravatars_title', '<h3 class="give-gravatars-title">' . esc_attr( $title ) . '</h3>' );
			} elseif ( isset( $give_options['give_donators_gravatars_heading'] ) ) {
				echo apply_filters( 'give_donators_gravatars_title', '<h3 class="give-gravatars-title">' . esc_attr( $give_options['give_donators_gravatars_heading'] ) . '</h2>' );
			}

		}
		echo '<ul class="give-purchase-gravatars-list">';
		$i = 0;

		if ( $payment_ids ) {
			foreach ( $payment_ids as $id ) {

				// Give saves a blank option even when the control is turned off, hence the extra check
				if ( isset( $give_options['give_donators_gravatars_maximum_number'] ) && '' != $give_options['give_donators_gravatars_maximum_number'] && $i == $give_options['give_donators_gravatars_maximum_number'] ) {
					continue;
				}

				// get the payment meta
				$payment_meta = get_post_meta( $id, '_give_payment_meta', true );

				// unserialize the payment meta
				$user_info = maybe_unserialize( $payment_meta['user_info'] );

				// get donor's first name
				$name = $user_info['first_name'];

				// get donor's email
				$email = get_post_meta( $id, '_give_payment_user_email', true );

				// set gravatar size and provide filter
				$size = isset( $give_options['give_donators_gravatars_gravatar_size'] ) ? apply_filters( 'give_donators_gravatars_gravatar_size', $give_options['give_donators_gravatars_gravatar_size'] ) : '';

				// default image
				$default_image = apply_filters( 'give_donators_gravatars_gravatar_default_image', false );

				// assemble output
				$output .= '<li>';

				$output .= get_avatar( $email, $size, $default_image, $name );
				$output .= '</li>';

				$i ++;

			} // end foreach
		}

		echo $output;
		echo '</ul>';
		echo '</div>';

		return apply_filters( 'give_donators_gravatars', ob_get_clean() );
	}

	/**
	 * Register widget
	 *
	 * @since 1.0
	 */
	function register_widget() {
		register_widget( 'Give_Donators_Gravatars_Widget' );
	}

	/**
	 * Shortcode
	 *
	 * @since 1.0
	 * @todo  set the ID to get_the_ID() if ID parameter is not passed through. Otherwise it will incorrectly get other gravatars
	 */
	function shortcode( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'id'    => '',
			'title' => ''
		), $atts, 'give_donators_gravatars' );

		// if no ID is passed on single give_forms pages, get the correct ID
		if ( is_singular( 'give_forms' ) ) {
			$id = get_the_ID();
		}

		$content = $this->gravatars( $atts['id'], $atts['title'] );

		return $content;

	}

	/**
	 * Settings
	 *
	 * @since 1.0
	 */
	function settings( $settings ) {

		$give_gravatar_settings = array(
			array(
				'name' => __( 'Donator Gravatars', 'give' ),
				'desc' => '<hr>',
				'id'   => 'give_title',
				'type' => 'give_title'
			),
			array(
				'name' => __( 'Heading', 'give' ),
				'desc' => __( 'The heading to display above the Gravatars', 'give' ),
				'type' => 'text',
				'id'   => 'give_donators_gravatars_heading'
			),
			array(
				'name'    => __( 'Gravatar Size', 'give' ),
				'desc'    => __( 'The size of each Gravatar in pixels (512px maximum)', 'give' ),
				'type'    => 'text_small',
				'id'      => 'give_donators_gravatars_gravatar_size',
				'default' => '64'
			),
			array(
				'name' => __( 'Minimum Unique Purchases Required', 'give' ),
				'desc' => sprintf( __( 'The minimum number of unique purchases a %s must have before the Gravatars are shown. Leave blank for no minimum.', 'give' ), strtolower( give_get_forms_label_singular() ) ),
				'type' => 'text_small',
				'id'   => 'give_donators_gravatars_min_purchases_required',
			),
			array(
				'name'    => __( 'Maximum Gravatars To Show', 'give' ),
				'desc'    => __( 'The maximum number of gravatars to show. Leave blank for no limit.', 'give' ),
				'type'    => 'text',
				'id'      => 'give_donators_gravatars_maximum_number',
				'default' => '20',
			),
			array(
				'name' => __( 'Gravatar Visibility', 'give' ),
				'desc' => __( 'Only show donators with a Gravatar account', 'give' ),
				'id'   => 'give_donators_gravatars_has_gravatar_account',
				'type' => 'checkbox',
			),
			array(
				'name' => __( 'Randomize Gravatars', 'give' ),
				'desc' => __( 'Randomize the Gravatars', 'give' ),
				'id'   => 'give_donators_gravatars_random_gravatars',
				'type' => 'checkbox',
			),
		);

		return array_merge( $settings, $give_gravatar_settings );
	}

}


/**
 * Widget
 *
 * @since 1.0
 */
class Give_Donators_Gravatars_Widget extends WP_Widget {

	/*
	 * widget constructor
	 */
	public function __construct() {

		$give_label_singular = function_exists( 'give_get_forms_label_singular' ) ? strtolower( give_get_forms_label_singular() ) : null;

		// widget settings
		$widget_ops = array(
			'classname'   => 'give-donators-gravatars',
			'description' => sprintf( __( 'Displays gravatars of people who have donated using your your %s. Will only show on the single %s page.', 'give' ), $give_label_singular, $give_label_singular )
		);

		// widget control settings
		$control_ops = array(
			'width'   => 250,
			'height'  => 350,
			'id_base' => 'give_gravatars_widget'
		);

		// create the widget
		parent::__construct(
			'give_donators_gravatars_widget',
			__( 'Give Donators Gravatars', 'give' ),
			$widget_ops,
			$control_ops
		);

	} // end constructor


	/*
	 * Outputs the content of the widget
	 */
	function widget( $args, $instance ) {
		global $give_options;

		//@TODO: Don't extract it!!!
		extract( $args );

		if ( ! is_singular( 'give_forms' ) ) {
			return;
		}

		// Variables from widget settings
		$title = apply_filters( 'widget_title', $instance['title'] );

		// Used by themes. Opens the widget
		echo $before_widget;

		// Display the widget title
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		$gravatars = new Give_Donators_Gravatars();

		echo $gravatars->gravatars( get_the_ID(), null ); // remove title

		// Used by themes. Closes the widget
		echo $after_widget;

	} // end WIDGET function

	/*
	 * Update function. Processes widget options to be saved
	 */
	function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;

	} // end UPDATE function

	/*
	 * Form function. Displays the actual form on the widget page
	 */
	function form( $instance ) {

		// Set up some default widget settings.
		$defaults = array(
			'title' => '',
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Title -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'give' ) ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>


		<?php
	} // end FORM function

}

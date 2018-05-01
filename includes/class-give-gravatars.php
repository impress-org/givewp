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
 * Give_Donors_Gravatars Class
 *
 * This class handles donors gravatars.
 *
 * @since 1.0
 */
class Give_Donors_Gravatars {

	/**
	 * Class Constructor
	 *
	 * Set up the Give Donors Gravatars Class.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since  1.0
	 * @access private
	 *
	 * @return void
	 */
	private function setup_actions() {
		//		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		//		add_shortcode( 'give_donors_gravatars', array( $this, 'shortcode' ) );
		//		add_filter( 'give_settings_display', array( $this, 'settings' ) );
		//		do_action( 'give_donors_gravatars_setup_actions' );
	}

	/**
	 * Utility function to check if a gravatar exists for a given email or id
	 *
	 * @see: https://gist.github.com/justinph/5197810
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int|string|object $id_or_email A user ID, email address, or comment object
	 *
	 * @return bool If the gravatar exists or not
	 */
	public function validate_gravatar( $id_or_email ) {
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
	 * Get an array of all the log IDs using the Give Logging Class
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $form_id Donation form id
	 *
	 * @return array        IDs if logs, false otherwise
	 */
	public function get_log_ids( $form_id = '' ) {
		// get log for this form
		$logs = Give()->logs->get_logs( $form_id );

		if ( $logs ) {
			$log_ids = array();

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
	 * @since  1.0
	 * @access public
	 *
	 * @param  int $form_id Donation form id
	 *
	 * @return mixed
	 */
	public function get_payment_ids( $form_id = '' ) {

		$give_options = give_get_settings();

		$log_ids = $this->get_log_ids( $form_id );

		if ( $log_ids ) {

			$payment_ids = array();

			foreach ( $log_ids as $id ) {
				// get the payment ID for each corresponding log ID
				$payment_ids[] = give_get_meta( $id, '_give_log_payment_id', true );
			}

			// remove donors who have donated more than once so we can have unique avatars
			$unique_emails = array();

			foreach ( $payment_ids as $key => $id ) {

				$email = give_get_meta( $id, '_give_payment_donor_email', true );

				if ( isset ( $give_options['give_donors_gravatars_has_gravatar_account'] ) ) {
					if ( ! $this->validate_gravatar( $email ) ) {
						continue;
					}
				}

				$unique_emails[ $id ] = give_get_meta( $id, '_give_payment_donor_email', true );

			}

			$unique_ids = array();

			// strip duplicate emails
			$unique_emails = array_unique( $unique_emails );

			// convert the unique IDs back into simple array
			foreach ( $unique_emails as $id => $email ) {
				$unique_ids[] = $id;
			}

			// randomize the payment IDs if enabled
			if ( isset( $give_options['give_donors_gravatars_random_gravatars'] ) ) {
				shuffle( $unique_ids );
			}

			// return our unique IDs
			return $unique_ids;

		}

	}

	/**
	 * Gravatars
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  int    $form_id Donation form id.
	 * @param  string $title   Donors gravatars title.
	 *
	 * @return string
	 */
	public function gravatars( $form_id = false, $title = '' ) {

		// unique $payment_ids 
		$payment_ids = $this->get_payment_ids( $form_id );

		$give_options = give_get_settings();

		// return if no ID
		if ( ! $form_id ) {
			return;
		}

		// minimum amount of donations before showing gravatars
		// if the number of items in array is not greater or equal to the number specified, then exit
		if ( isset( $give_options['give_donors_gravatars_min_purchases_required'] ) && '' != $give_options['give_donors_gravatars_min_purchases_required'] ) {
			if ( ! ( count( $payment_ids ) >= $give_options['give_donors_gravatars_min_purchases_required'] ) ) {
				return;
			}
		}

		ob_start();

		$output = '';
		echo '<div id="give-purchase-gravatars">';


		if ( isset ( $title ) ) {

			if ( $title ) {
				echo wp_kses_post( apply_filters( 'give_donors_gravatars_title', '<h3 class="give-gravatars-title">' . esc_attr( $title ) . '</h3>' ) );
			} elseif ( isset( $give_options['give_donors_gravatars_heading'] ) ) {
				echo wp_kses_post( apply_filters( 'give_donors_gravatars_title', '<h3 class="give-gravatars-title">' . esc_attr( $give_options['give_donors_gravatars_heading'] ) . '</h2>' ) );
			}

		}
		echo '<ul class="give-purchase-gravatars-list">';
		$i = 0;

		if ( $payment_ids ) {
			foreach ( $payment_ids as $id ) {

				// Give saves a blank option even when the control is turned off, hence the extra check
				if ( isset( $give_options['give_donors_gravatars_maximum_number'] ) && '' != $give_options['give_donors_gravatars_maximum_number'] && $i == $give_options['give_donors_gravatars_maximum_number'] ) {
					continue;
				}

				// get the payment meta
				$payment_meta = give_get_meta( $id, '_give_payment_meta', true );

				$user_info = $payment_meta['user_info'];

				// get donor's first name
				$name = $user_info['first_name'];

				// get donor's email
				$email = give_get_meta( $id, '_give_payment_donor_email', true );

				// set gravatar size and provide filter
				$size = isset( $give_options['give_donors_gravatars_gravatar_size'] ) ? apply_filters( 'give_donors_gravatars_gravatar_size', $give_options['give_donors_gravatars_gravatar_size'] ) : '';

				// default image
				$default_image = apply_filters( 'give_donors_gravatars_gravatar_default_image', false );

				// assemble output
				$output .= '<li>';

				$output .= get_avatar( $email, $size, $default_image, $name );
				$output .= '</li>';

				$i ++;

			} // end foreach
		}

		echo wp_kses_post( $output );
		echo '</ul>';
		echo '</div>';

		return apply_filters( 'give_donors_gravatars', ob_get_clean() );
	}

	/**
	 * Register widget
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return void
	 */
	public function register_widget() {
		register_widget( 'Give_Donors_Gravatars_Widget' );
	}

	/**
	 * Shortcode
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array  $atts    Shortcode attribures.
	 * @param  string $content Shortcode content.
	 *
	 * @return string
	 */
	public function shortcode( $atts, $content = null ) {

		$atts = shortcode_atts( array(
			'id'    => '',
			'title' => ''
		), $atts, 'give_donors_gravatars' );

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
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $settings Gravatar settings.
	 *
	 * @return array           Gravatar settings.
	 */
	public function settings( $settings ) {

		$give_gravatar_settings = array(
			array(
				'name' => esc_html__( 'Donator Gravatars', 'give' ),
				'desc' => '<hr>',
				'id'   => 'give_title',
				'type' => 'give_title'
			),
			array(
				'name' => esc_html__( 'Heading', 'give' ),
				'desc' => esc_html__( 'The heading to display above the Gravatars.', 'give' ),
				'type' => 'text',
				'id'   => 'give_donors_gravatars_heading'
			),
			array(
				'name'    => esc_html__( 'Gravatar Size', 'give' ),
				'desc'    => esc_html__( 'The size of each Gravatar in pixels (512px maximum).', 'give' ),
				'type'    => 'text_small',
				'id'      => 'give_donors_gravatars_gravatar_size',
				'default' => '64'
			),
			array(
				'name' => esc_html__( 'Minimum Unique Donations Required', 'give' ),
				'desc' => esc_html__( 'The minimum number of unique donations a form must have before the Gravatars are shown. Leave blank for no minimum.', 'give' ),
				'type' => 'text_small',
				'id'   => 'give_donors_gravatars_min_purchases_required',
			),
			array(
				'name'    => esc_html__( 'Maximum Gravatars To Show', 'give' ),
				'desc'    => esc_html__( 'The maximum number of gravatars to show. Leave blank for no limit.', 'give' ),
				'type'    => 'text',
				'id'      => 'give_donors_gravatars_maximum_number',
				'default' => '20',
			),
			array(
				'name' => esc_html__( 'Gravatar Visibility', 'give' ),
				'desc' => esc_html__( 'Show only donors with a Gravatar account.', 'give' ),
				'id'   => 'give_donors_gravatars_has_gravatar_account',
				'type' => 'checkbox',
			),
			array(
				'name' => esc_html__( 'Randomize Gravatars', 'give' ),
				'desc' => esc_html__( 'Randomize the Gravatars.', 'give' ),
				'id'   => 'give_donors_gravatars_random_gravatars',
				'type' => 'checkbox',
			),
		);

		return array_merge( $settings, $give_gravatar_settings );
	}

}


/**
 * Give_Donors_Gravatars_Widget Class
 *
 * This class handles donors gravatars
 *
 * @since 1.0
 */
class Give_Donors_Gravatars_Widget extends WP_Widget {

	/**
	 * Widget constructor
	 *
	 * @since  1.0
	 * @access public
	 */
	public function __construct() {

		// widget settings
		$widget_ops = array(
			'classname'   => 'give-donors-gravatars',
			'description' => esc_html__( 'Displays gravatars of people who have donated using your your form. Will only show on the single form page.', 'give' ),
		);

		// widget control settings
		$control_ops = array(
			'width'   => 250,
			'height'  => 350,
			'id_base' => 'give_gravatars_widget'
		);

		// create the widget
		parent::__construct(
			'give_donors_gravatars_widget',
			esc_html__( 'Give Donors Gravatars', 'give' ),
			$widget_ops,
			$control_ops
		);

	}

	/**
	 * Donors gravatars widget content
	 *
	 * Outputs the content of the widget
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
	 * @param  array $instance Settings for the current Links widget instance.
	 *
	 * @return void
	 */
	public function widget( $args, $instance ) {

		$defaults = array(
			'before_widget' => '',
			'after_widget' => '',
			'before_title' => '',
			'after_title' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( ! is_singular( 'give_forms' ) ) {
			return;
		}

		// Variables from widget settings
		$title = apply_filters( 'widget_title', $instance['title'] );

		$output = '';

		// Used by themes. Opens the widget
		$output .= $args['before_widget'];

		// Display the widget title
		if ( $title ) {
			$output .= $args['before_title'] . $title . $args['after_title'];
		}

		$gravatars = new Give_Donors_Gravatars();

		$output .= $gravatars->gravatars( get_the_ID(), null ); // remove title

		// Used by themes. Closes the widget
		$output .= $args['after_widget'];

		echo wp_kses_post( $output );
	}

	/**
	 * Update donors gravatars
	 *
	 * Processes widget options to be saved.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $new_instance New settings for this instance as input by the user via WP_Widget::form().
	 * @param  array $old_instance Old settings for this instance.
	 *
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title'] = wp_strip_all_tags( $new_instance['title'] );

		return $instance;

	}

	/**
	 * Output donors gravatars
	 *
	 * Displays the actual form on the widget page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $instance Current settings.
	 *
	 * @return void
	 */
	public function form( $instance ) {

		// Set up some default widget settings.
		$defaults = array(
			'title' => '',
		);

		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Title -->
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'give' ) ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['title'] ); ?>" />
		</p>

		<?php
	}

}

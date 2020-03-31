<?php
/**
 * Donors Gravatars
 *
 * @package     Give
 * @subpackage  Classes/Give_Donors_Gravatars
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donor_Wall_Widget Class
 *
 * This class handles donors gravatars
 *
 * @since 1.0
 */
class Give_Donor_Wall_Widget extends WP_Widget {

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
			'id_base' => 'give_gravatars_widget',
		);

		// Create the widget
		parent::__construct(
			'give_donors_gravatars_widget',
			esc_html__( 'GiveWP Donor Gravatars', 'give' ),
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

		// @TODO: Don't extract it!!!
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

		echo Give_Donor_Wall::get_instance()->gravatars( get_the_ID(), null ); // remove title

		// Used by themes. Closes the widget
		echo $after_widget;

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

		$instance['title'] = strip_tags( $new_instance['title'] );

		return $instance;

	}

	/**
	 * Output donors
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
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'give' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>

		<?php
	}

	/**
	 * Register the widget
	 *
	 * @return void
	 */
	function widget_init() {
		register_widget( $this->self );
	}

}




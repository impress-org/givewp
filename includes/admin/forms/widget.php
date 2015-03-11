<?php
/**
 * Give Form Widget
 *
 * @package     WordImpress
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2015, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the Give Forms Widget.
 *
 * @since 1.0
 * @return void
 */
function init_give_forms_widget() {
	register_widget( 'Give_Forms_Widget' );
}

add_action( 'widgets_init', 'init_give_forms_widget' );

/**
 *  Google Places Reviews
 *
 * @description: The Google Places Reviews
 * @since      : 1.0
 */
class Give_Forms_Widget extends WP_Widget {

	/**
	 * Array of Private Options
	 *
	 * @since    1.0.0
	 *
	 * @var array
	 */
	public $widget_fields = array(
		'title'        => '',
		'give_form_id' => '',
	);


	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'give_forms_widget', // Base ID
			__( 'Give Widget: Display a Form', 'give' ), // Name
			array(
				'classname'   => 'give-forms-widget',
				'description' => __( 'Display a Give Donation Form in your theme\'s widget powered sidebar.', 'give' )
			)
		);

	}


	/**
	 * Back-end widget form.
	 * @see WP_Widget::form()
	 */
	function form( $instance ) {


		//loop through options array and save options to new instance
		foreach ( $this->widget_fields as $field => $value ) {
			${$field} = ! isset( $instance[ $field ] ) ? $value : esc_attr( $instance[ $field ] );
		}
		echo Give()->html->product_dropdown( array( 'chosen' => false ) );



	} //end form function


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	function widget( $args, $instance ) {
		do_action( 'give_before_forms_widget' );

		echo "hello";

		do_action( 'give_after_forms_widget' );

	}


	/**
	 * Updates the widget options via foreach loop
	 *
	 * @DESC: Saves the widget options
	 * @SEE WP_Widget::update
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		//loop through options array and save to new instance
		foreach ( $this->widget_fields as $field => $value ) {
			$instance[ $field ] = strip_tags( stripslashes( $new_instance[ $field ] ) );
		}


		return $instance;
	}


}
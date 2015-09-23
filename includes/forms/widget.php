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
defined( 'ABSPATH' ) or die();

/**
 * Give Form widget
 *
 * @since 1.0
 */
class Give_Forms_Widget extends WP_Widget
{
	/**
	 * The widget class name
	 *
	 * @var string
	 */
	protected $self;

	/**
	 * Instantiate the class
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->self = get_class( $this );

		parent::__construct(
			strtolower( $this->self ),
			__( 'Give - Donation Form', 'give' ),
			array(
				'description' => __( 'Display a Give Donation Form in your theme\'s widget powered sidebar.', 'give' )
			)
		);

		add_action( 'widgets_init',          array( $this, 'widget_init' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_widget_scripts' ) );
	}

	/**
	 * Load widget assets only on the widget page
	 *
	 * @param string $hook
	 *
	 * @return void
	 */
	public function admin_widget_scripts( $hook )
	{
		// Directories of assets
		$js_dir     = GIVE_PLUGIN_URL . 'assets/js/admin/';
		$js_plugins = GIVE_PLUGIN_URL . 'assets/js/plugins/';
		$css_dir    = GIVE_PLUGIN_URL . 'assets/css/';

		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		// Widget Script
		if ( $hook == 'widgets.php' ) {

			wp_enqueue_style( 'give-qtip-css', $css_dir . 'jquery.qtip' . $suffix . '.css' );

			wp_enqueue_script( 'give-qtip', $js_plugins . 'jquery.qtip' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION );

			wp_enqueue_script( 'give-admin-widgets-scripts', $js_dir . 'admin-widgets' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
		}
	}

	/**
	 * Echo the widget content.
	 *
	 * @param array $args     Display arguments including before_title, after_title,
	 *                        before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance )
	{
		extract( $args );

		$title        = !empty( $instance['title'] ) ? $instance['title'] : '';
		$form_id      = !empty( $instance['form_id'] ) ? $instance['form_id'] : false;
		$float_labels = !empty( $instance['float_labels'] ) ? $instance['float_labels'] : '';

		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

		echo $before_widget;

		do_action( 'give_before_forms_widget' );

		echo $title ? $before_title . $title . $after_title : '';

		give_get_donation_form( $instance );

		echo $after_widget;

		do_action( 'give_after_forms_widget' );
	}

	/**
	 * Output the settings update form.
	 *
	 * @param array $instance Current settings.
	 *
	 * @return string
	 */
	public function form( $instance )
	{
		$defaults = array(
			'title'        => '',
			'form_id'      => '',
			'float_labels' => '',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		extract( $instance );

		// Query Give Forms

		$args = array(
			'post_type'      => 'give_forms',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		$give_forms = get_posts( $args );

		// Widget: Title

		?><p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'give' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" /><br>
			<small><?php _e( 'Leave blank to hide the widget title.', 'give' ); ?></small>
		</p><?php

		// Widget: Give Form

		?><p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'form_id' ) ); ?>"><?php printf( __( 'Give %s', 'give' ), give_get_forms_label_singular() ); ?></label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'form_id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'form_id' ) ); ?>">
				<option value="current"><?php _e( 'Please select...', 'give' ); ?></option>
				<?php foreach ( $give_forms as $give_form ) { ?>
					<option <?php selected( absint( $form_id ), $give_form->ID ); ?> value="<?php echo esc_attr( $give_form->ID ); ?>"><?php echo $give_form->post_title; ?></option>
				<?php } ?>
			</select><br>
			<small><?php _e( 'Select a Give Form that you would like to embed in this widget area.', 'give' ); ?></small>
		</p><?php

		// Widget: Floating Labels

		?><p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>"><?php _e( 'Floating Labels', 'give' ); ?></label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'float_labels' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>">
				<option value="global"><?php _e( 'Use the global floating label setting', 'give' ); ?></option>
				<option value="local"><?php _e( 'Use the form\'s floating label setting', 'give' ); ?></option>
				<option value="enabled"><?php _e( 'Enable floating labels', 'give' ); ?></option>
				<option value="disabled"><?php _e( 'Disable floating labels', 'give' ); ?></option>
			</select><br>
			<small><?php _e( 'Select the <a href="http://bradfrost.com/blog/post/float-label-pattern/" target="_blank">floating labels</a> setting for this Give form.', 'give' ); ?></small>
		</p><?php
	}

	/**
	 * Register the widget
	 *
	 * @return void
	 */
	function widget_init()
	{
		register_widget( $this->self );
	}

	/**
	 * Update the widget
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance )
	{
		$this->flush_widget_cache();

		return $new_instance;
	}

	/**
	 * Flush widget cache
	 *
	 * @return void
	 */
	public function flush_widget_cache()
	{
		wp_cache_delete( $this->self, 'widget' );
	}
}

new Give_Forms_Widget;

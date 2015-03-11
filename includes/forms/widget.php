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
	public $widget_defaults = array(
		'title' => '',
		'id'    => '',
	);


	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'give_forms_widget', // Base ID
			__( 'Give - Donation Form', 'give' ), // Name
			array(
				'classname'   => 'give-forms-widget',
				'description' => __( 'Display a Give Donation Form in your theme\'s widget powered sidebar.', 'give' )
			) //Args
		);

		//Actions
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_widget_scripts' ) );


	}

	//Load Widget JS Script ONLY on Widget page
	public function admin_widget_scripts( $hook ) {

		//Directories of assets
		$js_dir     = GIVE_PLUGIN_URL . 'assets/js/admin/';
		$js_plugins = GIVE_PLUGIN_URL . 'assets/js/plugins/';
		$css_dir    = GIVE_PLUGIN_URL . 'assets/css/';

		// Use minified libraries if SCRIPT_DEBUG is turned off
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		//Widget Script
		if ( $hook == 'widgets.php' ) {
			wp_enqueue_script( 'give-qtip', $js_plugins . 'jquery.qtip' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION );
			wp_enqueue_script( 'give-admin-widgets-scripts', $js_dir . 'admin-widgets' . $suffix . '.js', array( 'jquery' ), GIVE_VERSION, false );
		}


	}


	/**
	 * Back-end widget form.
	 *
	 * @param array $instance
	 *
	 * @return null
	 * @see WP_Widget::form()
	 */
	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, $this->widget_defaults ); ?>

		<!-- Title -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title', 'gpr' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
			       name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance['title']; ?>" />
		</p>


		<?php
		//Query Give Forms
		$args       = array(
			'post_type'      => 'give_forms',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);
		$give_forms = get_posts( $args );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php printf( __( 'Give %s', 'give' ), give_get_forms_label_singular() ); ?>
				<span class="dashicons dashicons-tinymce-help" data-tooltip="<?php _e( 'Select a Give Form that you would like to embed in this widget area.', 'give' ); ?>"></span>
			</label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>">
				<option value="current"><?php _e( 'Please select...', 'give' ); ?></option>
				<?php foreach ( $give_forms as $give_form ) { ?>
					<option <?php selected( absint( $instance['id'] ), $give_form->ID ); ?> value="<?php echo esc_attr( $give_form->ID ); ?>"><?php echo $give_form->post_title; ?></option>
				<?php } ?>
			</select>
		</p>
		<!-- Give Form Field -->

	<?php
	} //end form function


	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		echo $args['before_widget'];

		do_action( 'give_before_forms_widget' );

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
		give_get_donation_form( $instance );

		echo $args['after_widget'];

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
		foreach ( $this->widget_defaults as $field => $value ) {
			$instance[ $field ] = strip_tags( stripslashes( $new_instance[ $field ] ) );
		}

		return $instance;

	}


}
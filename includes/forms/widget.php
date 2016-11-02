<?php
/**
 * Give Form Widget
 *
 * @package     WordImpress
 * @subpackage  Admin/Forms
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give Form widget
 *
 * @since 1.0
 */
class Give_Forms_Widget extends WP_Widget{
	/**
	 * The widget class name
	 *
	 * @var string
	 */
	protected $self;

	/**
	 * Instantiate the class
	 */
	public function __construct(){
		$this->self = get_class( $this );

		parent::__construct(
			strtolower( $this->self ),
			esc_html__( 'Give - Donation Form', 'give' ),
			array(
				'description' => esc_html__( 'Display a Give Donation Form in your theme\'s widget powered sidebar.', 'give' )
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
	public function admin_widget_scripts( $hook ){
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
	public function widget( $args, $instance ){
		$title = !empty( $instance['title'] ) ? $instance['title'] : '';
		$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );


		// If user set float labels to global then check global float label setting and update donation form widget accordingly.
		if( ( 'global' === $instance['float_labels'] ) ) {
			$instance['float_labels'] = ( 'on' === give_get_option( 'enable_floatlabels', '' ) ) ? 'enabled' : 'disabled';
		}

		echo $args['before_widget'];

		/**
		 * Fires before widget settings form in the admin area.
		 *
		 * @since 1.0
		 */
		do_action( 'give_before_forms_widget' );

		echo $title ? $args['before_title'] . $title . $args['after_title'] : '';

		give_get_donation_form( $instance );

		echo $args['after_widget'];

		/**
		 * Fires after widget settings form in the admin area.
		 *
		 * @since 1.0
		 */
		do_action( 'give_after_forms_widget' );
	}

	/**
	 * Output the settings update form.
	 *
	 * @param array $instance Current settings.
	 *
	 * @return string
	 */
	public function form( $instance ){
		$defaults = array(
			'title'         => '',
			'id'            => '',
			'float_labels'  => 'global',
			'display_style' => 'modal',
		);

		$instance = wp_parse_args( (array) $instance, $defaults );

		// Backward compatibility: Set float labels as default if, it was set as empty previous.
		$instance['float_labels'] = empty( $instance['float_labels'] ) ? 'global' : $instance['float_labels'];

		// Query Give Forms
		$args = array(
			'post_type'      => 'give_forms',
			'posts_per_page' => - 1,
			'post_status'    => 'publish',
		);

		$give_forms = get_posts( $args );

		// Widget: Title

		?><p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:', 'give' ); ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php esc_attr_e( $instance['title'] ); ?>" /><br>
			<small class="give-field-description"><?php esc_html_e( 'Leave blank to hide the widget title.', 'give' ); ?></small>
		</p><?php

		// Widget: Give Form

		?><p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>"><?php esc_html_e( 'Give Form:', 'give' ); ?></label>
			<select class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'id' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'id' ) ); ?>">
				<option value="current"><?php esc_html_e( '- Select -', 'give' ); ?></option>
				<?php foreach ( $give_forms as $give_form ) { ?>
					<option <?php selected( absint( $instance['id'] ), $give_form->ID ); ?> value="<?php echo esc_attr( $give_form->ID ); ?>"><?php echo $give_form->post_title; ?></option>
				<?php } ?>
			</select><br>
			<small class="give-field-description"><?php esc_html_e( 'Select a Give Form to embed in this widget.', 'give' ); ?></small>
		</p>

		<?php // Widget: Display Style ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'display_style' ) ); ?>"><?php esc_html_e( 'Display style:', 'give' ); ?></label><br>
			<label for="<?php echo $this->get_field_id( 'display_style' ); ?>-onpage"><input type="radio" class="widefat" id="<?php echo $this->get_field_id( 'display_style' ); ?>-onpage" name="<?php echo $this->get_field_name( 'display_style' ); ?>" value="onpage" <?php checked( $instance['display_style'], 'onpage' ); ?>> <?php echo esc_html__( 'All Fields', 'give' ); ?></label>
			&nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'display_style' ); ?>-reveal"><input type="radio" class="widefat" id="<?php echo $this->get_field_id( 'display_style' ); ?>-reveal" name="<?php echo $this->get_field_name( 'display_style' ); ?>" value="reveal" <?php checked( $instance['display_style'], 'reveal' ); ?>> <?php echo esc_html__( 'Reveal', 'give' ); ?></label>
			&nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'display_style' ); ?>-modal"><input type="radio" class="widefat" id="<?php echo $this->get_field_id( 'display_style' ); ?>-modal" name="<?php echo $this->get_field_name( 'display_style' ); ?>" value="modal" <?php checked( $instance['display_style'], 'modal' ); ?>> <?php echo esc_html__( 'Modal', 'give' ); ?></label><br>
			 <small class="give-field-description">
				<?php echo esc_html__( 'Select a Give Form style.', 'give' ); ?>
			</small>
		</p>

		<?php // Widget: Floating Labels ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'float_labels' ) ); ?>"><?php esc_html_e( 'Floating Labels (optional):', 'give' ); ?></label><br>
			<label for="<?php echo $this->get_field_id( 'float_labels' ); ?>-global"><input type="radio" class="widefat" id="<?php echo $this->get_field_id( 'float_labels' ); ?>-global" name="<?php echo $this->get_field_name( 'float_labels' ); ?>" value="global" <?php checked( $instance['float_labels'], 'global' ); ?>> <?php echo esc_html__( 'Global Options', 'give' ); ?></label>
			&nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'float_labels' ); ?>-enabled"><input type="radio" class="widefat" id="<?php echo $this->get_field_id( 'float_labels' ); ?>-enabled" name="<?php echo $this->get_field_name( 'float_labels' ); ?>" value="enabled" <?php checked( $instance['float_labels'], 'enabled' ); ?>> <?php echo esc_html__( 'Yes', 'give' ); ?></label>
			&nbsp;&nbsp;<label for="<?php echo $this->get_field_id( 'float_labels' ); ?>-disabled"><input type="radio" class="widefat" id="<?php echo $this->get_field_id( 'float_labels' ); ?>-disabled" name="<?php echo $this->get_field_name( 'float_labels' ); ?>" value="disabled" <?php checked( $instance['float_labels'], 'disabled' ); ?>> <?php echo esc_html__( 'No', 'give' ); ?></label><br>
			<small class="give-field-description">
				<?php
				printf(
					/* translators: %s: https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels */
					__( 'Override the <a href="%s" target="_blank">floating labels</a> setting for this Give form.', 'give' ),
					esc_url( 'https://givewp.com/documentation/core/give-forms/creating-give-forms/#floating-labels' )
				);
			?></small>
		</p><?php
	}

	/**
	 * Register the widget
	 *
	 * @return void
	 */
	function widget_init(){
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
	public function update( $new_instance, $old_instance ){
		$this->flush_widget_cache();

		return $new_instance;
	}

	/**
	 * Flush widget cache
	 *
	 * @return void
	 */
	public function flush_widget_cache(){
		wp_cache_delete( $this->self, 'widget' );
	}
}

new Give_Forms_Widget;

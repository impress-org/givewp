<?php
/**
* Donation Form Data
*
* Displays the form data box, tabbed, with several panels.
*
* @author   WordImpress
* @version  1.8
*/

/**
* Give_Meta_Box_Form_Data Class.
*/
class Give_MetaBox_Form_Data {

	/**
	 * Meta box settings.
	 *
	 * @var array
	 */
	private $settings = array();


	private $metabox_id;
	private $metabox_label;
	private $post_types;


	/**
	 * Give_MetaBox_Form_Data constructor.
	 */
	function __construct(){
		$this->metabox_id    = 'give-metabox-form-data';
		$this->metabox_label = esc_html__( 'Donation Form Data', 'give' );
		$this->post_types    = array( 'give_forms' );

		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 30 );

		// Load required scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );

		// cmb2 old setting loaders.
		add_filter( 'give_metabox_form_data_settings', array( $this, 'cmb2_metabox_settings' ) );
	}

	/**
	 *
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->get_metabox_ID(),
			$this->get_metabox_label(),
			array( $this, 'output' ),
			$this->get_allowed_post_types(),
			'normal',
			'high'
		);
	}


	/**
	 *
	 */
	function enqueue_script() {
		global $post;

		if( is_object( $post ) && 'give_forms' === $post->post_type ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}
	}

	/**
	 * @return string
	 */
	function get_metabox_ID() {
		return $this->metabox_id;
	}

	/**
	 * @return string
	 */
	function get_metabox_label() {
		return $this->metabox_label;
	}

	/**
	 * @return array
	 */
	function get_allowed_post_types() {
		return $this->post_types;
	}

	public function get_tabs() {
		$this->settings = apply_filters( 'give_metabox_form_data_settings', array() );

		$tabs = array();

		if( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				$tabs[] = array(
					'id'    => $setting['id'],
					'label' => $setting['title'],
				);
			}
		}

		return apply_filters( 'give_metabox_form_data_setting_tabs', $tabs );
	}

	/**
	 *
	 */
	public function output() {
		// Bailout.
		if( $form_data_tabs = $this->get_tabs() ) {
			wp_nonce_field( 'give_save_form_meta', 'give_meta_nonce' );
			?>
			<div class="panel-wrap form_data">
				<ul class="form_data_tabs give-metabox-tabs">
					<?php foreach ( $form_data_tabs as $index => $form_data_tab ) : ?>
						<li class="<?php echo "{$form_data_tab['id']}_tab" . ( ! $index ? ' active' : '' ); ?>"><a href="#<?php echo $form_data_tab['id']; ?>"><?php echo $form_data_tab['label']; ?></a></li>
					<?php endforeach; ?>
				</ul>

				<?php $show_first_tab_content = true; ?>
				<?php foreach ( $this->settings as $setting ) : ?>
					<?php do_action( "give_before_{$setting['id']}_settings" ); ?>

					<div id="<?php echo $setting['id']; ?>" class="panel give_options_panel <?php echo ( $show_first_tab_content ? '' : 'give-hidden' ); $show_first_tab_content = false; ?>">
						<?php if( ! empty( $setting['fields'] ) ) : ?>
							<?php foreach ( $setting['fields'] as $field ) : ?>
								<?php $this->render_field( $field ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>

					<?php do_action( "give_after_{$setting['id']}_settings" ); ?>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}

	/**
	 * This function add backward compatibility to render cmb2 type field type
	 *
	 * @param  array $field Field argument array.
	 * @return bool
	 */
	function render_field( $field ) {
		$func_name_prefix = 'give_wp';
		$func_name = '';

		// Set callback function on basis of cmb2 field name.
		switch( $field['type'] ) {
			case 'radio_inline':
				$func_name              = "{$func_name_prefix}_radio";
				$field['wrapper_class'] = 'give-inline-radio-fields';
				//$field['name']          = $field['id'];
				break;

			case 'text':
			case 'text-medium':
			case 'text-small' :
			case 'text_small' :
				$field['type'] = 'text';
				$func_name = "{$func_name_prefix}_text_input";
				break;


			case 'textarea' :
				$func_name = "{$func_name_prefix}_textarea_input";
				break;

			case 'colorpicker' :
				$func_name      = "{$func_name_prefix}_{$field['type']}";
				$field['type'] = 'text';
				$field['class'] = 'give-colorpicker';
				break;

			default:
				$func_name = "{$func_name_prefix}_{$field['type']}";
		}

		// Check if render callback exist or not.
		if ( !  function_exists( "$func_name" ) || empty( $func_name ) ){
			return false;
		}

		// Add support to define field description by desc & description param.
		$field['description'] = ( ! empty( $field['description'] )
			? $field['description']
			: ( ! empty( $field['desc'] ) ? $field['desc'] : '' ) );

		// Call render function.
		$func_name( $field );

		return true;
	}

	/**
	 * CMB2 setting tab loader
	 */
	function cmb2_metabox_settings() {
		return apply_filters( 'cmb2_meta_boxes', array() );

	}
}

new Give_MetaBox_Form_Data();


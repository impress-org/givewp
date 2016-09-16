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
	 * @since 1.8
	 * @var   array
	 */
	private $settings = array();

	/**
	 * Metabox ID.
	 *
	 * @since 1.8
	 * @var   string
	 */
	private $metabox_id;

	/**
	 * Metabox Label.
	 *
	 * @since 1.8
	 * @var   string
	 */
	private $metabox_label;


	/**
	 * Give_MetaBox_Form_Data constructor.
	 */
	function __construct(){
		$this->metabox_id    = 'give-metabox-form-data';
		$this->metabox_label = esc_html__( 'Donation Form Data', 'give' );

		// Setup.
		add_action( 'admin_init' , array( $this, 'setup' ) );

		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 30 );

		// Load required scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );
		
		// Save form meta.
		add_action( 'save_post_give_forms', array( $this, 'save' ), 10, 2 );

		// cmb2 old setting loaders.
		add_filter( 'give_metabox_form_data_settings', array( $this, 'cmb2_metabox_settings' ) );
	}


	/**
	 * Setup metabox related data.
	 *
	 * @since  1.8
	 * @return void
	 */
	function setup() {
		$this->settings = $this->get_settings();
	}


	/**
	 * Get metabox settings
	 *
	 * @since  1.8
	 * @return mixed|void
	 */
	function get_settings() {
		/**
		 * Filter the metabox tabbed panel settings.
		 */
		$settings = apply_filters( 'give_metabox_form_data_settings', array() );

		foreach ( $settings as $key => $setting ) {

			/**
			 * Filter the each metabox tab panel setting.
			 */
			$settings[$key] = apply_filters( "give_form_data_setting_{$key}", $setting );
		}
		return $settings;
	}

	/**
	 * Add metabox.
	 *
	 * @since  1.8
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->get_metabox_ID(),
			$this->get_metabox_label(),
			array( $this, 'output' ),
			array( 'give_forms' ),
			'normal',
			'high'
		);
	}


	/**
	 * Enqueue scripts.
	 *
	 * @since  1.8
	 * @return void
	 */
	function enqueue_script() {
		global $post;

		if( is_object( $post ) && 'give_forms' === $post->post_type ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
		}
	}

	/**
	 * Get metabox id.
	 *
	 * @since  1.8
	 * @return string
	 */
	function get_metabox_ID() {
		return $this->metabox_id;
	}

	/**
	 * Get metabox label.
	 *
	 * @since  1.8
	 * @return string
	 */
	function get_metabox_label() {
		return $this->metabox_label;
	}


	/**
	 * Get metabox tabs.
	 *
	 * @since  1.8
	 * @return mixed|void
	 */
	public function get_tabs() {
		$tabs = array();

		if( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				$tabs[] = array(
					'id'    => $setting['id'],
					'label' => $setting['title'],
				);
			}
		}


		return $tabs;
	}

	/**
	 * Output metabox settings.
	 *
	 * @since  1.8
	 * @return void
	 */
	public function output() {
		// Bailout.
		if( $form_data_tabs = $this->get_tabs() ) {
			wp_nonce_field( 'give_save_form_meta', 'give_form_meta_nonce' );
			?>
			<div class="give-metabox-panel-wrap">
				<ul class="give-form-data-tabs give-metabox-tabs">
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
								<?php give_render_field( $field ); ?>
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
	 * CMB2 settings loader.
	 *
	 * @since  1.8
	 * @return mixed|void
	 */
	function cmb2_metabox_settings() {
		$all_cmb2_settings = apply_filters( 'cmb2_meta_boxes', array() );
		$give_forms_settings = $all_cmb2_settings;


		// Filter settings: Use only give forms related settings.
		foreach ( $all_cmb2_settings as $index => $setting ) {
			if( ! in_array( 'give_forms', $setting['object_types'] ) ) {
				unset( $give_forms_settings[$index] );
			}
		}
		return $give_forms_settings;

	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @since  1.8
	 * @param  int $post_id
	 * @param  object $post
	 * @return void
	 */
	public function save( $post_id, $post ) {

		// $post_id and $post are required.
		if ( empty( $post_id ) || empty( $post ) ) {
			return;
		}
		
		// Don't save meta boxes for revisions or autosaves.
		if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
			return;
		}

		// Check the nonce.
		if ( empty( $_POST['give_form_meta_nonce'] ) || ! wp_verify_nonce( $_POST['give_form_meta_nonce'], 'give_save_form_meta' ) ) {
			return;
		}

		// Check the post being saved == the $post_id to prevent triggering this call for other save_post events.
		if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
			return;
		}

		// Check user has permission to edit.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Fire action before saving form meta.
		do_action( 'give_pre_process_give_forms_meta', $post_id, $post );

		/**
		 * Filter the meta key to save.
		 * Third party addon developer can remove there meta keys from this array to handle saving data on there own.
		 */
		$form_meta_keys = apply_filters( 'give_process_form_meta_keys', $this->get_meta_keys_from_settings() );

		// Save form meta data.
		// @TODO: Sanitize data for group field, just in case if there any editor setting field.
		if( ! empty( $form_meta_keys ) ) {
			foreach ( $form_meta_keys as $form_meta_key ) {
				if( isset( $_POST[ $form_meta_key ] ) ) {
					if( $field_type = $this->get_field_type( $form_meta_key ) ) {
						switch ( $field_type ) {
							case 'wysiwyg':
								$form_meta_value = wp_kses_post( $_POST[ $form_meta_key ] );
								update_post_meta( $post_id, $form_meta_key, $form_meta_value );
								break;

							default:
								$form_meta_value = give_clean( $_POST[ $form_meta_key ] );
								update_post_meta( $post_id, $form_meta_key, $form_meta_value );
						}
					}
				}
			}
		}

		// Fire action after saving form meta.
		do_action( 'give_post_process_give_forms_meta', $post_id, $post );
	}


	/**
	 * Get all setting field ids.
	 *
	 * @since  1.8
	 * @return array
	 */
	private function get_meta_keys_from_settings() {
		$meta_keys = array();
		foreach ( $this->settings as $setting ) {
			if( ! empty( $setting['fields'] ) ) {
				foreach ( $setting['fields'] as $field ) {
					$meta_keys[] = $field['id'];
				}
			}
		}

		return $meta_keys;
	}


	/**
	 * Get field type.
	 *
	 * @since  1.8
	 * @param  $field_id
	 * @return array|string
	 */
	function get_field_type( $field_id ) {
		$field_type = '';

		if( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				if( ! empty( $setting['fields'] ) ) {
					foreach ( $setting['fields'] as $field ) {
						$field_type[] = $field['type'];
						return $field_type;
					}
				}
			}
		}
	}
}

new Give_MetaBox_Form_Data();


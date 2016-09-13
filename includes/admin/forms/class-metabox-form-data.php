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

	/**
	 * Metabox ID.
	 * @var string
	 */
	private $metabox_id;

	/**
	 * Metabox Label.
	 * @var string
	 */
	private $metabox_label;


	/**
	 * Give_MetaBox_Form_Data constructor.
	 */
	function __construct(){
		$this->metabox_id    = 'give-metabox-form-data';
		$this->metabox_label = esc_html__( 'Donation Form Data', 'give' );

		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 30 );

		// Load required scripts.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_script' ) );

		// cmb2 old setting loaders.
		add_filter( 'give_metabox_form_data_settings', array( $this, 'cmb2_metabox_settings' ) );
	}

	/**
	 * Add metabox.
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
	 * @return string
	 */
	function get_metabox_ID() {
		return $this->metabox_id;
	}

	/**
	 * Get metabox label.
	 *
	 * @return string
	 */
	function get_metabox_label() {
		return $this->metabox_label;
	}


	/**
	 * Get metabox tabs.
	 * @return mixed|void
	 */
	public function get_tabs() {
		/**
		 * Filter the metabox settings.
		 */
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

		/**
		 * Filter the metabox tabs.
		 */
		return apply_filters( 'give_metabox_form_data_setting_tabs', $tabs );
	}

	/**
	 * Output metabox settings.
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
	 * CMB2 setting tab loader.
	 *
	 * @return mixed|void
	 */
	function cmb2_metabox_settings() {
		return apply_filters( 'cmb2_meta_boxes', array() );

	}
}

new Give_MetaBox_Form_Data();


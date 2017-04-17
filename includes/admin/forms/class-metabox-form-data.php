<?php
/**
 * Donation Form Data
 *
 * Displays the form data box, tabbed, with several panels.
 *
 * @package     Give
 * @subpackage  Classes/Give_MetaBox_Form_Data
 * @copyright   Copyright (c) 2016, WordImpress
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
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
	function __construct() {
		$this->metabox_id    = 'give-metabox-form-data';
		$this->metabox_label = esc_html__( 'Donation Form Options', 'give' );

		// Setup.
		add_action( 'admin_init', array( $this, 'setup' ) );

		// Add metabox.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ), 30 );

		// Save form meta.
		add_action( 'save_post_give_forms', array( $this, 'save' ), 10, 2 );

		// cmb2 old setting loaders.
		// add_filter( 'give_metabox_form_data_settings', array( $this, 'cmb2_metabox_settings' ) );
		// Add offline donations options.
		add_filter( 'give_metabox_form_data_settings', array( $this, 'add_offline_donations_setting_tab' ), 0, 1 );
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
	 * @return array
	 */
	function get_settings() {
		$post_id               = give_get_admin_post_id();
		$price                 = give_get_form_price( $post_id );
		$custom_amount_minimum = give_get_form_minimum_price( $post_id );
		$goal                  = give_get_form_goal( $post_id );

		// No empty prices - min. 1.00 for new forms
		if ( empty( $price ) && is_null( $post_id ) ) {
			$price = esc_attr( give_format_decimal( '1.00' ) );
		}

		// Min. $1.00 for new forms
		if ( empty( $custom_amount_minimum ) ) {
			$custom_amount_minimum = esc_attr( give_format_decimal( '1.00' ) );
		}

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_give_';

		$settings = array(
			/**
			 * Repeatable Field Groups
			 */
			'form_field_options'    => apply_filters( 'give_forms_field_options', array(
				'id'        => 'form_field_options',
				'title'     => esc_html__( 'Donation Options', 'give' ),
				'icon-html' => '<span class="give-icon give-icon-heart"></span>',
				'fields'    => apply_filters( 'give_forms_donation_form_metabox_fields', array(
					// Donation Option
					array(
						'name'        => esc_html__( 'Donation Option', 'give' ),
						'description' => esc_html__( 'Do you want this form to have one set donation price or multiple levels (for example, $10, $20, $50)?', 'give' ),
						'id'          => $prefix . 'price_option',
						'type'        => 'radio_inline',
						'default'     => 'set',
						'options'     => apply_filters( 'give_forms_price_options', array(
							'set'   => esc_html__( 'Set Donation', 'give' ),
							'multi' => esc_html__( 'Multi-level Donation', 'give' ),
						) ),
					),
					array(
						'name'        => esc_html__( 'Set Donation', 'give' ),
						'description' => esc_html__( 'This is the set donation amount for this form. If you have a "Custom Amount Minimum" set, make sure it is less than this amount.', 'give' ),
						'id'          => $prefix . 'set_price',
						'type'        => 'text_small',
						'data_type'   => 'price',
						'attributes'  => array(
							'placeholder' => give_format_decimal( '1.00' ),
							'value'       => give_format_decimal( $price ),
							'class'       => 'give-money-field',
						),
					),
					// Display Style
					array(
						'name'        => esc_html__( 'Display Style', 'give' ),
						'description' => esc_html__( 'Set how the donations levels will display on the form.', 'give' ),
						'id'          => $prefix . 'display_style',
						'type'        => 'radio_inline',
						'default'     => 'buttons',
						'options'     => array(
							'buttons'  => esc_html__( 'Buttons', 'give' ),
							'radios'   => esc_html__( 'Radios', 'give' ),
							'dropdown' => esc_html__( 'Dropdown', 'give' ),
						),
					),
					// Custom Amount
					array(
						'name'        => esc_html__( 'Custom Amount', 'give' ),
						'description' => esc_html__( 'Do you want the user to be able to input their own donation amount?', 'give' ),
						'id'          => $prefix . 'custom_amount',
						'type'        => 'radio_inline',
						'default'     => 'disabled',
						'options'     => array(
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						),
					),
					array(
						'name'        => esc_html__( 'Minimum Amount', 'give' ),
						'description' => esc_html__( 'Enter the minimum custom donation amount.', 'give' ),
						'id'          => $prefix . 'custom_amount_minimum',
						'type'        => 'text_small',
						'data_type'   => 'price',
						'attributes'  => array(
							'placeholder' => give_format_decimal( '1.00' ),
							'value'       => give_format_decimal( $custom_amount_minimum ),
							'class'       => 'give-money-field',
						),
					),
					array(
						'name'        => esc_html__( 'Custom Amount Text', 'give' ),
						'description' => esc_html__( 'This text appears as a label below the custom amount field for set donation forms. For multi-level forms the text will appear as it\'s own level (ie button, radio, or select option).', 'give' ),
						'id'          => $prefix . 'custom_amount_text',
						'type'        => 'text_medium',
						'attributes'  => array(
							'rows'        => 3,
							'placeholder' => esc_attr__( 'Give a Custom Amount', 'give' ),
						),
					),
					// Donation Levels: Repeatable CMB2 Group
					array(
						'id'      => $prefix . 'donation_levels',
						'type'    => 'group',
						'options' => array(
							'add_button'    => esc_html__( 'Add Level', 'give' ),
							'header_title'  => esc_html__( 'Donation Level', 'give' ),
							'remove_button' => '<span class="dashicons dashicons-no"></span>',
						),
						// Fields array works the same, except id's only need to be unique for this group.
						// Prefix is not needed.
						'fields'  => apply_filters( 'give_donation_levels_table_row', array(
							array(
								'name' => esc_html__( 'ID', 'give' ),
								'id'   => $prefix . 'id',
								'type' => 'levels_id',
							),
							array(
								'name'       => esc_html__( 'Amount', 'give' ),
								'id'         => $prefix . 'amount',
								'type'       => 'text_small',
								'data_type'  => 'price',
								'attributes' => array(
									'placeholder' => give_format_decimal( '1.00' ),
									'class'       => 'give-money-field',
								),
							),
							array(
								'name'       => esc_html__( 'Text', 'give' ),
								'id'         => $prefix . 'text',
								'type'       => 'text',
								'attributes' => array(
									'placeholder' => esc_html__( 'Donation Level', 'give' ),
									'class'       => 'give-multilevel-text-field',
								),
							),
							array(
								'name' => esc_html__( 'Default', 'give' ),
								'id'   => $prefix . 'default',
								'type' => 'give_default_radio_inline',
							),
						) ),
					),
					array(
						'name'  => 'donation_options_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.givewp.com/form-donation-options',
						'title' => esc_html__( 'Donation Options', 'give' ),
					),
				),
					$post_id
				),
			) ),

			/**
			 * Display Options
			 */
			'form_display_options'  => apply_filters( 'give_form_display_options', array(
					'id'        => 'form_display_options',
					'title'     => esc_html__( 'Form Display', 'give' ),
					'icon-html' => '<span class="give-icon give-icon-display"></span>',
					'fields'    => apply_filters( 'give_forms_display_options_metabox_fields', array(
						array(
							'name'    => esc_html__( 'Display Options', 'give' ),
							'desc'    => sprintf( __( 'How would you like to display donation information for this form?', 'give' ), '#' ),
							'id'      => $prefix . 'payment_display',
							'type'    => 'radio_inline',
							'options' => array(
								'onpage' => esc_html__( 'All Fields', 'give' ),
								'modal'  => esc_html__( 'Modal', 'give' ),
								'reveal' => esc_html__( 'Reveal', 'give' ),
								'button' => esc_html__( 'Button', 'give' ),
							),
							'default' => 'onpage',
						),
						array(
							'id'         => $prefix . 'reveal_label',
							'name'       => esc_html__( 'Continue Button', 'give' ),
							'desc'       => esc_html__( 'The button label for displaying the additional payment fields.', 'give' ),
							'type'       => 'text_small',
							'attributes' => array(
								'placeholder' => esc_attr__( 'Donate Now', 'give' ),
							),
						),
						array(
							'id'         => $prefix . 'checkout_label',
							'name'       => esc_html__( 'Submit Button', 'give' ),
							'desc'       => esc_html__( 'The button label for completing a donation.', 'give' ),
							'type'       => 'text_small',
							'attributes' => array(
								'placeholder' => esc_html__( 'Donate Now', 'give' ),
							),
						),
						array(
							'name' => esc_html__( 'Default Gateway', 'give' ),
							'desc' => esc_html__( 'By default, the gateway for this form will inherit the global default gateway (set under Give > Settings > Payment Gateways). This option allows you to customize the default gateway for this form only.', 'give' ),
							'id'   => $prefix . 'default_gateway',
							'type' => 'default_gateway',
						),
						array(
							'name'    => esc_html__( 'Guest Donations', 'give' ),
							'desc'    => esc_html__( 'Do you want to allow non-logged-in users to make donations?', 'give' ),
							'id'      => $prefix . 'logged_in_only',
							'type'    => 'radio_inline',
							'default' => 'enabled',
							'options' => array(
								'enabled'  => esc_html__( 'Enabled', 'give' ),
								'disabled' => esc_html__( 'Disabled', 'give' ),
							),
						),
						array(
							'name'    => esc_html__( 'Registration', 'give' ),
							'desc'    => esc_html__( 'Display the registration and login forms in the payment section for non-logged-in users.', 'give' ),
							'id'      => $prefix . 'show_register_form',
							'type'    => 'radio',
							'options' => array(
								'none'         => esc_html__( 'None', 'give' ),
								'registration' => esc_html__( 'Registration', 'give' ),
								'login'        => esc_html__( 'Login', 'give' ),
								'both'         => esc_html__( 'Registration + Login', 'give' ),
							),
							'default' => 'none',
						),
						array(
							'name'    => esc_html__( 'Floating Labels', 'give' ),
							/* translators: %s: forms http://docs.givewp.com/form-floating-labels */
							'desc'    => sprintf( __( 'Select the <a href="%s" target="_blank">floating labels</a> setting for this Give form. Be aware that if you have the "Disable CSS" option enabled, you will need to style the floating labels yourself.', 'give' ), esc_url( 'http://docs.givewp.com/form-floating-labels' ) ),
							'id'      => $prefix . 'form_floating_labels',
							'type'    => 'radio_inline',
							'options' => array(
								'global'   => esc_html__( 'Global Option', 'give' ),
								'enabled'  => esc_html__( 'Enabled', 'give' ),
								'disabled' => esc_html__( 'Disabled', 'give' ),
							),
							'default' => 'global',
						),
						array(
							'name'  => 'form_display_docs',
							'type'  => 'docs_link',
							'url'   => 'http://docs.givewp.com/form-display-options',
							'title' => esc_html__( 'Form Display', 'give' ),
						),
					),
						$post_id
					),
				)
			),

			/**
			 * Donation Goals
			 */
			'donation_goal_options' => apply_filters( 'give_donation_goal_options', array(
				'id'        => 'donation_goal_options',
				'title'     => esc_html__( 'Donation Goal', 'give' ),
				'icon-html' => '<span class="give-icon give-icon-target"></span>',
				'fields'    => apply_filters( 'give_forms_donation_goal_metabox_fields', array(
					// Goals
					array(
						'name'        => esc_html__( 'Donation Goal', 'give' ),
						'description' => esc_html__( 'Do you want to set a donation goal for this form?', 'give' ),
						'id'          => $prefix . 'goal_option',
						'type'        => 'radio_inline',
						'default'     => 'disabled',
						'options'     => array(
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						),
					),
					array(
						'name'        => esc_html__( 'Goal Amount', 'give' ),
						'description' => esc_html__( 'This is the monetary goal amount you want to reach for this form.', 'give' ),
						'id'          => $prefix . 'set_goal',
						'type'        => 'text_small',
						'data_type'   => 'price',
						'attributes'  => array(
							'placeholder' => give_format_decimal( '0.00' ),
							'value'       => give_format_decimal( $goal ),
							'class'       => 'give-money-field',
						),
					),

					array(
						'name'        => esc_html__( 'Goal Format', 'give' ),
						'description' => esc_html__( 'Do you want to display the total amount raised based on your monetary goal or a percentage? For instance, "$500 of $1,000 raised" or "50% funded".', 'give' ),
						'id'          => $prefix . 'goal_format',
						'type'        => 'radio_inline',
						'default'     => 'amount',
						'options'     => array(
							'amount'     => esc_html__( 'Amount', 'give' ),
							'percentage' => esc_html__( 'Percentage', 'give' ),
						),
					),
					array(
						'name'    => esc_html__( 'Progress Bar Color', 'give' ),
						'desc'    => esc_html__( 'Customize the color of the goal progress bar.', 'give' ),
						'id'      => $prefix . 'goal_color',
						'type'    => 'colorpicker',
						'default' => '#2bc253',
					),

					array(
						'name'    => esc_html__( 'Close Form', 'give' ),
						'desc'    => esc_html__( 'Do you want to close the donation forms and stop accepting donations once this goal has been met?', 'give' ),
						'id'      => $prefix . 'close_form_when_goal_achieved',
						'type'    => 'radio_inline',
						'default' => 'disabled',
						'options' => array(
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						),
					),
					array(
						'name'       => __( 'Goal Achieved Message', 'give' ),
						'desc'       => __( 'Do you want to display a custom message when the goal is closed?', 'give' ),
						'id'         => $prefix . 'form_goal_achieved_message',
						'type'       => 'wysiwyg',
                        'default' => __( 'Thank you to all our donors, we have met our fundraising goal.', 'give' ),
					),
					array(
						'name'  => 'donation_goal_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.givewp.com/form-donation-goal',
						'title' => esc_html__( 'Donation Goal', 'give' ),
					),
				),
					$post_id
				),
			) ),

			/**
			 * Content Field
			 */
			'form_content_options'  => apply_filters( 'give_forms_content_options', array(
				'id'        => 'form_content_options',
				'title'     => esc_html__( 'Form Content', 'give' ),
				'icon-html' => '<span class="give-icon give-icon-edit"></span>',
				'fields'    => apply_filters( 'give_forms_content_options_metabox_fields', array(

					// Donation content.
					array(
						'name'        => esc_html__( 'Display Content', 'give' ),
						'description' => esc_html__( 'Do you want to add custom content to this form?', 'give' ),
						'id'          => $prefix . 'display_content',
						'type'        => 'radio_inline',
						'options'     => array(
							'enabled'  => esc_html__( 'Enabled', 'give' ),
							'disabled' => esc_html__( 'Disabled', 'give' ),
						),
						'default'     => 'disabled',
					),

					// Content placement.
					array(
						'name'        => esc_html__( 'Content Placement', 'give' ),
						'description' => esc_html__( 'This option controls where the content appears within the donation form.', 'give' ),
						'id'          => $prefix . 'content_placement',
						'type'        => 'radio_inline',
						'options'     => apply_filters( 'give_forms_content_options_select', array(
								'give_pre_form'  => esc_html__( 'Above fields', 'give' ),
								'give_post_form' => esc_html__( 'Below fields', 'give' ),
							)
						),
						'default'     => 'give_pre_form',
					),
					array(
						'name'        => esc_html__( 'Content', 'give' ),
						'description' => esc_html__( 'This content will display on the single give form page.', 'give' ),
						'id'          => $prefix . 'form_content',
						'type'        => 'wysiwyg',
					),
					array(
						'name'  => 'form_content_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.givewp.com/form-content',
						'title' => esc_html__( 'Form Content', 'give' ),
					),
				),
					$post_id
				),
			) ),

			/**
			 * Terms & Conditions
			 */
			'form_terms_options'    => apply_filters( 'give_forms_terms_options', array(
				'id'        => 'form_terms_options',
				'title'     => esc_html__( 'Terms & Conditions', 'give' ),
				'icon-html' => '<span class="give-icon give-icon-checklist"></span>',
				'fields'    => apply_filters( 'give_forms_terms_options_metabox_fields', array(
					// Donation Option
					array(
						'name'        => esc_html__( 'Terms and Conditions', 'give' ),
						'description' => esc_html__( 'Do you want to require the donor to accept terms prior to being able to complete their donation?', 'give' ),
						'id'          => $prefix . 'terms_option',
						'type'        => 'radio_inline',
						'options'     => apply_filters( 'give_forms_content_options_select', array(
								'global'   => esc_html__( 'Global Option', 'give' ),
								'enabled'  => esc_html__( 'Customize', 'give' ),
								'disabled' => esc_html__( 'Disable', 'give' ),
							)
						),
						'default'     => 'global',
					),
					array(
						'id'         => $prefix . 'agree_label',
						'name'       => esc_html__( 'Agreement Label', 'give' ),
						'desc'       => esc_html__( 'The label shown next to the agree to terms check box. Add your own to customize or leave blank to use the default text placeholder.', 'give' ),
						'type'       => 'text',
						'size'       => 'regular',
						'attributes' => array(
							'placeholder' => esc_attr__( 'Agree to Terms?', 'give' ),
						),
					),
					array(
						'id'   => $prefix . 'agree_text',
						'name' => esc_html__( 'Agreement Text', 'give' ),
						'desc' => esc_html__( 'This is the actual text which the user will have to agree to in order to make a donation.', 'give' ),
						'type' => 'wysiwyg',
					),
					array(
						'name'  => 'terms_docs',
						'type'  => 'docs_link',
						'url'   => 'http://docs.givewp.com/form-terms',
						'title' => esc_html__( 'Terms and Conditions', 'give' ),
					),
				),
					$post_id
				),
			) ),
		);


		/**
		 * Filter the metabox tabbed panel settings.
		 */
		$settings = apply_filters( 'give_metabox_form_data_settings', $settings, $post_id );

		// Output.
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

		if ( is_object( $post ) && 'give_forms' === $post->post_type ) {

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
	 * @return array
	 */
	public function get_tabs() {
		$tabs = array();

		if ( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				if ( ! isset( $setting['id'] ) || ! isset( $setting['title'] ) ) {
					continue;
				}
				$tab = array(
					'id'        => $setting['id'],
					'label'     => $setting['title'],
					'icon-html' => ( ! empty( $setting['icon-html'] ) ? $setting['icon-html'] : '' ),
				);

				if ( $this->has_sub_tab( $setting ) ) {
					if ( empty( $setting['sub-fields'] ) ) {
						$tab = array();
					} else {
						foreach ( $setting['sub-fields'] as $sub_fields ) {
							$tab['sub-fields'][] = array(
								'id'        => $sub_fields['id'],
								'label'     => $sub_fields['title'],
								'icon-html' => ( ! empty( $sub_fields['icon-html'] ) ? $sub_fields['icon-html'] : '' ),
							);
						}
					}
				}

				if ( ! empty( $tab ) ) {
					$tabs[] = $tab;
				}
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
		if ( $form_data_tabs = $this->get_tabs() ) {
			wp_nonce_field( 'give_save_form_meta', 'give_form_meta_nonce' );
			?>
			<div class="give-metabox-panel-wrap">
				<ul class="give-form-data-tabs give-metabox-tabs">
					<?php foreach ( $form_data_tabs as $index => $form_data_tab ) : ?>
						<li class="<?php echo "{$form_data_tab['id']}_tab" . ( ! $index ? ' active' : '' ) . ( $this->has_sub_tab( $form_data_tab ) ? ' has-sub-fields' : '' ); ?>">
							<a href="#<?php echo $form_data_tab['id']; ?>">
								<?php if ( ! empty( $form_data_tab['icon-html'] ) ) : ?>
									<?php echo $form_data_tab['icon-html']; ?>
								<?php else : ?>
									<span class="give-icon give-icon-default"></span>
								<?php endif; ?>
								<span class="give-label"><?php echo $form_data_tab['label']; ?></span>
							</a>
							<?php if ( $this->has_sub_tab( $form_data_tab ) ) : ?>
								<ul class="give-metabox-sub-tabs give-hidden">
									<?php foreach ( $form_data_tab['sub-fields'] as $sub_tab ) : ?>
										<li class="<?php echo "{$sub_tab['id']}_tab"; ?>">
											<a href="#<?php echo $sub_tab['id']; ?>">
												<?php if ( ! empty( $sub_tab['icon-html'] ) ) : ?>
													<?php echo $sub_tab['icon-html']; ?>
												<?php else : ?>
													<span class="give-icon give-icon-default"></span>
												<?php endif; ?>
												<span class="give-label"><?php echo $sub_tab['label']; ?></span>
											</a>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>

				<?php $show_first_tab_content = true; ?>
				<?php foreach ( $this->settings as $setting ) : ?>
					<?php if ( ! $this->has_sub_tab( $setting ) ) : ?>
						<?php do_action( "give_before_{$setting['id']}_settings" ); ?>

						<div id="<?php echo $setting['id']; ?>"
							 class="panel give_options_panel<?php echo( $show_first_tab_content ? '' : ' give-hidden' );
						     $show_first_tab_content = false; ?>">
							<?php if ( ! empty( $setting['fields'] ) ) : ?>
								<?php foreach ( $setting['fields'] as $field ) : ?>
									<?php give_render_field( $field ); ?>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>

						<?php do_action( "give_after_{$setting['id']}_settings" ); ?>
					<?php else: ?>
						<?php if ( $this->has_sub_tab( $setting ) ) : ?>
							<?php if ( ! empty( $setting['sub-fields'] ) ) : ?>
								<?php foreach ( $setting['sub-fields'] as $index => $sub_fields ) : ?>
									<div id="<?php echo $sub_fields['id']; ?>"
										 class="panel give_options_panel give-hidden">
										<?php if ( ! empty( $sub_fields['fields'] ) ) : ?>
											<?php foreach ( $sub_fields['fields'] as $sub_field ) : ?>
												<?php give_render_field( $sub_field ); ?>
											<?php endforeach; ?>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}


	/**
	 * Check if setting field has sub tabs/fields
	 *
	 * @since 1.8
	 *
	 * @param $field_setting
	 *
	 * @return bool
	 */
	private function has_sub_tab( $field_setting ) {
		$has_sub_tab = false;
		if ( array_key_exists( 'sub-fields', $field_setting ) ) {
			$has_sub_tab = true;
		}

		return $has_sub_tab;
	}

	/**
	 * CMB2 settings loader.
	 *
	 * @since  1.8
	 * @return array
	 */
	function cmb2_metabox_settings() {
		$all_cmb2_settings   = apply_filters( 'cmb2_meta_boxes', array() );
		$give_forms_settings = $all_cmb2_settings;

		// Filter settings: Use only give forms related settings.
		foreach ( $all_cmb2_settings as $index => $setting ) {
			if ( ! in_array( 'give_forms', $setting['object_types'] ) ) {
				unset( $give_forms_settings[ $index ] );
			}
		}

		return $give_forms_settings;

	}

	/**
	 * Check if we're saving, the trigger an action based on the post type.
	 *
	 * @since  1.8
	 *
	 * @param  int    $post_id
	 * @param  object $post
	 *
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
		if ( ! empty( $form_meta_keys ) ) {
			foreach ( $form_meta_keys as $form_meta_key ) {

				// Set default value for checkbox fields.
				if (
					! isset( $_POST[ $form_meta_key ] )
					&& ( 'checkbox' === $this->get_field_type( $form_meta_key ) )
				) {
					$_POST[ $form_meta_key ] = '';
				}

				if ( isset( $_POST[ $form_meta_key ] ) ) {
					if ( $field_type = $this->get_field_type( $form_meta_key ) ) {
						switch ( $field_type ) {
							case 'textarea':
							case 'wysiwyg':
								$form_meta_value = wp_kses_post( $_POST[ $form_meta_key ] );
								update_post_meta( $post_id, $form_meta_key, $form_meta_value );
								break;

							case 'group':
								$form_meta_value = array();

								foreach ( $_POST[ $form_meta_key ] as $index => $group ) {

									// Do not save template input field values.
									if ( '{{row-count-placeholder}}' === $index ) {
										continue;
									}

									$group_meta_value = array();
									foreach ( $group as $field_id => $field_value ) {
										switch ( $this->get_field_type( $field_id, $form_meta_key ) ) {
											case 'wysiwyg':
												$group_meta_value[ $field_id ] = wp_kses_post( $field_value );
												break;

											default:
												$group_meta_value[ $field_id ] = give_clean( $field_value );
										}
									}

									if ( ! empty( $group_meta_value ) ) {
										$form_meta_value[ $index ] = $group_meta_value;
									}
								}


								// Arrange repeater field keys in order.
								$form_meta_value = array_values( $form_meta_value );

								// Save data.
								update_post_meta( $post_id, $form_meta_key, $form_meta_value );
								break;

							default:
								$form_meta_value = give_clean( $_POST[ $form_meta_key ] );

								// Save data.
								update_post_meta( $post_id, $form_meta_key, $form_meta_value );
						}

						// Fire after saving form meta key.
						do_action( "give_save_{$form_meta_key}", $form_meta_key, $form_meta_value, $post_id, $post );
					}
				}
			}
		}

		// Fire action after saving form meta.
		do_action( 'give_post_process_give_forms_meta', $post_id, $post );
	}


	/**
	 * Get field ID.
	 *
	 * @since 1.8
	 *
	 * @param array $field
	 *
	 * @return string
	 */
	private function get_field_id( $field ) {
		$field_id = '';

		if ( array_key_exists( 'id', $field ) ) {
			$field_id = $field['id'];

		}

		return $field_id;
	}

	/**
	 * Get fields ID.
	 *
	 * @since 1.8
	 *
	 * @param $setting
	 *
	 * @return array
	 */
	private function get_fields_id( $setting ) {
		$meta_keys = array();

		if ( ! empty( $setting ) ) {
			foreach ( $setting['fields'] as $field ) {
				if ( $field_id = $this->get_field_id( $field ) ) {
					$meta_keys[] = $field_id;
				}
			}
		}

		return $meta_keys;
	}

	/**
	 * Get sub fields ID.
	 *
	 * @since 1.8
	 *
	 * @param $setting
	 *
	 * @return array
	 */
	private function get_sub_fields_id( $setting ) {
		$meta_keys = array();

		if ( $this->has_sub_tab( $setting ) && ! empty( $setting['sub-fields'] ) ) {
			foreach ( $setting['sub-fields'] as $fields ) {
				if ( ! empty( $fields['fields'] ) ) {
					foreach ( $fields['fields'] as $field ) {
						if ( $field_id = $this->get_field_id( $field ) ) {
							$meta_keys[] = $field_id;
						}
					}
				}
			}
		}

		return $meta_keys;
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
			if ( $this->has_sub_tab( $setting ) ) {
				$meta_key = $this->get_sub_fields_id( $setting );
			} else {
				$meta_key = $this->get_fields_id( $setting );
			}

			$meta_keys = array_merge( $meta_keys, $meta_key );
		}

		return $meta_keys;
	}


	/**
	 * Get field type.
	 *
	 * @since  1.8
	 *
	 * @param  string $field_id
	 * @param  string $group_id
	 *
	 * @return string
	 */
	function get_field_type( $field_id, $group_id = '' ) {
		$field = $this->get_setting_field( $field_id, $group_id );

		$type = array_key_exists( 'type', $field )
			? $field['type']
			: '';

		return $type;
	}


	/**
	 * Get Field
	 *
	 * @since 1.8
	 *
	 * @param array  $setting
	 * @param string $field_id
	 *
	 * @return array
	 */
	private function get_field( $setting, $field_id ) {
		$setting_field = array();

		if ( ! empty( $setting['fields'] ) ) {
			foreach ( $setting['fields'] as $field ) {
				if ( array_key_exists( 'id', $field ) && $field['id'] === $field_id ) {
					$setting_field = $field;
					break;
				}
			}
		}

		return $setting_field;
	}

	/**
	 * Get Sub Field
	 *
	 * @since 1.8
	 *
	 * @param array  $setting
	 * @param string $field_id
	 *
	 * @return array
	 */
	private function get_sub_field( $setting, $field_id ) {
		$setting_field = array();

		if ( ! empty( $setting['sub-fields'] ) ) {
			foreach ( $setting['sub-fields'] as $fields ) {
				if ( $field = $this->get_field( $fields, $field_id ) ) {
					$setting_field = $field;
					break;
				}
			}
		}

		return $setting_field;
	}

	/**
	 * Get setting field.
	 *
	 * @since  1.8
	 *
	 * @param  string $field_id
	 * @param  string $group_id Get sub field from group.
	 *
	 * @return array
	 */
	function get_setting_field( $field_id, $group_id = '' ) {
		$setting_field = array();

		$_field_id = $field_id;
		$field_id  = empty( $group_id ) ? $field_id : $group_id;

		if ( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				if (
					( $this->has_sub_tab( $setting ) && ( $setting_field = $this->get_sub_field( $setting, $field_id ) ) )
					|| ( $setting_field = $this->get_field( $setting, $field_id ) )
				) {
					break;
				}
			}
		}


		// Get field from group.
		if ( ! empty( $group_id ) ) {
			foreach ( $setting_field['fields'] as $field ) {
				if ( array_key_exists( 'id', $field ) && $field['id'] === $_field_id ) {
					$setting_field = $field;
				}
			}
		}

		return $setting_field;
	}


	/**
	 * Add offline donations setting tab to donation form options metabox.
	 *
	 * @since  1.8
	 *
	 * @param  array $settings List of form settings.
	 *
	 * @return mixed
	 */
	function add_offline_donations_setting_tab( $settings ) {
		if ( give_is_gateway_active( 'offline' ) ) {
			$settings['offline_donations_options'] = apply_filters( 'give_forms_offline_donations_options', array(
				'id'        => 'offline_donations_options',
				'title'     => esc_html__( 'Offline Donations', 'give' ),
				'icon-html' => '<span class="give-icon give-icon-purse"></span>',
				'fields'    => apply_filters( 'give_forms_offline_donations_metabox_fields', array() ),
			) );
		}

		return $settings;
	}
}

new Give_MetaBox_Form_Data();


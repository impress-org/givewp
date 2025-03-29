<?php
/**
 * Donation Form Data
 *
 * Displays the form data box, tabbed, with several panels.
 *
 * @package     Give
 * @subpackage  Classes/Give_MetaBox_Form_Data
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */

use Give\Form\Template;
use Give\FormAPI\Fields;
use Give\FormAPI\Section;
use Give\Helpers\Form\Template as FormTemplateUtils;
use Give\Views\Admin\UpsellNotice;

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
	private $settings = [];

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
		$this->metabox_label = __( 'Donation Form Options', 'give' );

		// Setup.
		add_action( 'admin_init', [ $this, 'setup' ] );

		// Add metabox.
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ], 10 );

		// Save form meta.
		add_action( 'save_post_give_forms', [ $this, 'save' ], 10, 2 );

		add_action( 'give_save__give_form_template', [ $this, 'save_form_template_settings' ], 10, 3 );

		// cmb2 old setting loaders.
		// add_filter( 'give_metabox_form_data_settings', array( $this, 'cmb2_metabox_settings' ) );
		// Add offline donations options.
		add_filter( 'give_metabox_form_data_settings', [ $this, 'add_offline_donations_setting_tab' ], 0, 1 );

		// Maintain active tab query parameter after save.
		add_filter( 'redirect_post_location', [ $this, 'maintain_active_tab' ], 10, 2 );
	}

	/**
	 * Setup metabox related data.
	 *
	 * @since 1.8
	 *
	 * @return void
	 */
	function setup() {
		$this->settings = $this->get_settings();
	}


	/**
     * Get metabox settings
     *
     * @since      1.8
     *
     * @return array
     */
	function get_settings() {
		$post_id           = give_get_admin_post_id();
		$price_placeholder = give_format_decimal( '1.00', false, false );

		// Start with an underscore to hide fields from custom fields list
		$prefix = '_give_';

		$settings = [
			/**
			 * Theme Options
			 */
			'form_template_options' => apply_filters(
				'give_form_template_options',
				[
					'id'        => 'form_template_options',
					'title'     => __( 'Form Template', 'give' ),
					'icon-html' => '<i class="fas fa-palette"></i>',
					'fields'    => [
						[
							'id'      => $prefix . 'form_template',
							'name'    => 'form_template',
							'type'    => 'hidden',
							'default' => '',
						],
					],
				]
			),

			/**
			 * Repeatable Field Groups
			 */
			'form_field_options'    => apply_filters(
				'give_forms_field_options',
                [
                    'id' => 'form_field_options',
                    'title' => __('Donation Options', 'give'),
                    'icon-html' => '<i class="fas fa-heart"></i>',
                    'fields' => apply_filters(
                        'give_forms_donation_form_metabox_fields',
                        [
                            // Donation Option.
                            [
                                'name' => __('Donation Option', 'give'),
                                'description' => __(
                                    'Do you want this form to have one set donation price or multiple levels (for example, $10, $20, $50)?',
                                    'give'
                                ),
                                'id' => $prefix . 'price_option',
                                'type' => 'radio_inline',
                                'default' => 'multi',
                                'options' => apply_filters(
                                    'give_forms_price_options',
                                    [
                                        'multi' => __('Multi-level Donation', 'give'),
                                        'set' => __('Set Donation', 'give'),
                                    ]
                                ),
                            ],
                            [
                                'name' => __('Set Donation', 'give'),
                                'description' => __(
                                    'This is the set donation amount for this form. If you have a "Custom Amount Minimum" set, make sure it is less than this amount.',
                                    'give'
                                ),
                                'id' => $prefix . 'set_price',
                                'default' => give_format_decimal(['amount' => '25.00']),
                                'type' => 'text_small',
                                'data_type' => 'price',
                                'attributes' => [
                                    'placeholder' => $price_placeholder,
                                    'class' => 'give-money-field',
                                ],
                                'wrapper_class' => 'give-hidden',
                            ],
                            // Custom Amount.
                            [
                                'name' => __('Custom Amount', 'give'),
                                'description' => __(
                                    'Do you want the user to be able to input their own donation amount?',
                                    'give'
                                ),
                                'id' => $prefix . 'custom_amount',
                                'type' => 'radio_inline',
                                'default' => 'enabled',
                                'options' => [
                                    'enabled' => __('Enabled', 'give'),
                                    'disabled' => __('Disabled', 'give'),
                                ],
                            ],
                            [
                                'name' => __('Donation Limit', 'give'),
                                'description' => __('Set the minimum and maximum amount for all gateways.', 'give'),
                                'id' => $prefix . 'custom_amount_range',
                                'type' => 'donation_limit',
                                'wrapper_class' => 'give-hidden',
                                'data_type' => 'price',
                                'attributes' => [
                                    'placeholder' => $price_placeholder,
                                    'class' => 'give-money-field',
                                ],
                                'options' => [
                                    'display_label' => __('Donation Limits: ', 'give'),
                                    'minimum' => give_format_decimal(['amount' => '5.00']),
                                    'maximum' => give_format_decimal(['amount' => '999999.99']),
                                ],
                            ],
                            [
                                'name' => __('Custom Amount Text', 'give'),
                                'description' => __(
                                    'This text appears as a label below the custom amount field for set donation forms. For multi-level forms the text will appear as its own level (button, radio, or select option).',
                                    'give'
                                ),
                                'id' => $prefix . 'custom_amount_text',
                                'type' => 'text_medium',
                                'default' => __('Custom Amount', 'give'),
                                'attributes' => [
                                    'rows' => 3,
                                ],
                                'wrapper_class' => 'give-hidden',
                            ],
                            // Donation Levels.
                            [
                                'id' => $prefix . 'donation_levels',
                                'type' => 'group',
                                'options' => [
                                    'add_button' => __('Add Level', 'give'),
                                    'header_title' => __('Donation Level', 'give'),
                                    'remove_button' => '<span class="dashicons dashicons-no"></span>',
                                ],
                                'default' => [
                                    [
                                        '_give_id' =>
                                            [
                                                'level_id' => 0,
                                            ],
                                        '_give_amount' => '10.000000',
                                    ],
                                    [
                                        '_give_id' =>
                                            [
                                                'level_id' => 1,
                                            ],
                                        '_give_amount' => '25.000000',
                                    ],
                                    [
                                        '_give_id' =>
                                            [
                                                'level_id' => 2,
                                            ],
                                        '_give_amount' => '50.000000',
                                    ],
                                    [
                                        '_give_id' =>
                                            [
                                                'level_id' => 3,
                                            ],
                                        '_give_amount' => '100.000000',
                                        '_give_default' => 'default',
                                    ],
                                    [
                                        '_give_id' =>
                                            [
                                                'level_id' => 5,
                                            ],
                                        '_give_amount' => '250.000000',
                                    ],
                                ],
                                'wrapper_class' => 'give-hidden',
                                // Fields array works the same, except id's only need to be unique for this group.
                                // Prefix is not needed.
                                'fields' => apply_filters(
                                    'give_donation_levels_table_row',
                                    [
                                        [
                                            'name' => __('ID', 'give'),
                                            'id' => $prefix . 'id',
                                            'type' => 'levels_id',
                                        ],
                                        [
                                            'name' => __('Amount', 'give'),
                                            'id' => $prefix . 'amount',
                                            'type' => 'text_small',
                                            'data_type' => 'price',
                                            'attributes' => [
                                                'placeholder' => $price_placeholder,
                                                'class' => 'give-money-field',
                                            ],
                                        ],
                                        [
                                            'name' => __('Text', 'give'),
                                            'id' => $prefix . 'text',
                                            'type' => 'text',
                                            'attributes' => [
                                                'placeholder' => __('Donation Level', 'give'),
                                                'class' => 'give-multilevel-text-field',
                                            ],
                                        ],
                                        [
                                            'name' => __('Default', 'give'),
                                            'id' => $prefix . 'default',
                                            'type' => 'give_default_radio_inline',
                                        ],
                                    ]
                                ),
                            ],
                            [
                                'name' => 'donation_options_docs',
                                'type' => 'docs_link',
                                'url' => 'http://docs.givewp.com/form-donation-options',
                                'title' => __('Donation Options', 'give'),
                            ],
                        ],
                        $post_id
                    ),
                ]
			),

			/**
			 * Display Options
			 */
			'form_display_options'  => apply_filters(
				'give_form_display_options',
				[
					'id'        => 'form_display_options',
					'title'     => __( 'Form Fields', 'give' ),
					'icon-html' => '<i class="fas fa-stream"></i>',
					'fields'    => apply_filters(
						'give_forms_display_options_metabox_fields',
						[
							[
								'name' => __( 'Default Gateway', 'give' ),
								'desc' => __( 'By default, the gateway for this form will inherit the global default gateway (set under GiveWP > Settings > Payment Gateways). This option allows you to customize the default gateway for this form only.', 'give' ),
								'id'   => $prefix . 'default_gateway',
								'type' => 'default_gateway',
							],
							[
								'name'    => __( 'Name Title Prefix', 'give' ),
								'desc'    => __( 'Do you want to add a name title prefix dropdown field before the donor\'s first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for the donor to choose from.', 'give' ),
								'id'      => $prefix . 'name_title_prefix',
								'type'    => 'radio_inline',
								'options' => [
									'global'   => __( 'Global Option', 'give' ),
									'required' => __( 'Required', 'give' ),
									'optional' => __( 'Optional', 'give' ),
									'disabled' => __( 'Disabled', 'give' ),
								],
								'default' => 'global',
							],
							[
								'name'                => __( 'Title Prefixes', 'give' ),
								'desc'                => __( 'Add or remove salutations from the dropdown using the field above.', 'give' ),
								'id'                  => $prefix . 'title_prefixes',
								'type'                => 'chosen',
								'data_type'           => 'multiselect',
								'allow-custom-values' => true,
								'style'               => 'width: 100%',
								'wrapper_class'       => 'give-hidden give-title-prefixes-wrap',
								'options'             => give_get_default_title_prefixes(),
							],
							[
								'name'    => __( 'Company Donations', 'give' ),
								'desc'    => __( 'Do you want a Company field to appear after First Name and Last Name?', 'give' ),
								'id'      => $prefix . 'company_field',
								'type'    => 'radio_inline',
								'default' => 'global',
								'options' => [
									'global'   => __( 'Global Option', 'give' ),
									'required' => __( 'Required', 'give' ),
									'optional' => __( 'Optional', 'give' ),
									'disabled' => __( 'Disabled', 'give' ),

								],
							],
							[
								'name'    => __( 'Last Name Field Required', 'give' ),
								'desc'    => __( 'Do you want to force the Last Name field to be required?', 'give' ),
								'id'      => $prefix . 'last_name_field_required',
								'type'    => 'radio_inline',
								'default' => 'global',
								'options' => [
									'global'   => __( 'Global Option', 'give' ),
									'required' => __( 'Required', 'give' ),
									'optional' => __( 'Optional', 'give' ),
								],
							],
							[
								'name'    => __( 'Anonymous Donations', 'give' ),
								'desc'    => __( 'Do you want to provide donors the ability mark themselves anonymous while giving? This will prevent their information from appearing publicly on your website but you will still receive their information for your records in the admin panel.', 'give' ),
								'id'      => "{$prefix}anonymous_donation",
								'type'    => 'radio_inline',
								'default' => 'global',
								'options' => [
									'global'   => __( 'Global Option', 'give' ),
									'enabled'  => __( 'Enabled', 'give' ),
									'disabled' => __( 'Disabled', 'give' ),
								],
							],
							[
								'name'    => __( 'Donor Comments', 'give' ),
								'desc'    => __( 'Do you want to provide donors the ability to add a comment to their donation? The comment will display publicly on the donor wall if they do not select to give anonymously.', 'give' ),
								'id'      => "{$prefix}donor_comment",
								'type'    => 'radio_inline',
								'default' => 'global',
								'options' => [
									'global'   => __( 'Global Option', 'give' ),
									'enabled'  => __( 'Enabled', 'give' ),
									'disabled' => __( 'Disabled', 'give' ),
								],
							],
							[
								'name'    => __( 'Guest Donations', 'give' ),
								'desc'    => __( 'Do you want to allow non-logged-in users to make donations?', 'give' ),
								'id'      => $prefix . 'logged_in_only',
								'type'    => 'radio_inline',
								'default' => 'enabled',
								'options' => [
									'enabled'  => __( 'Enabled', 'give' ),
									'disabled' => __( 'Disabled', 'give' ),
								],
							],
							[
								'name'    => __( 'Registration', 'give' ),
								'desc'    => __( 'Display the registration and login forms in the payment section for non-logged-in users.', 'give' ),
								'id'      => $prefix . 'show_register_form',
								'type'    => 'radio',
								'options' => [
									'none'         => __( 'None', 'give' ),
									'registration' => __( 'Registration', 'give' ),
									'login'        => __( 'Login', 'give' ),
									'both'         => __( 'Registration + Login', 'give' ),
								],
								'default' => 'none',
							],
							[
								'name'  => 'form_display_docs',
								'type'  => 'docs_link',
								'url'   => 'http://docs.givewp.com/form-display-options',
								'title' => __( 'Form Display', 'give' ),
							],
						],
						$post_id
					),
				]
			),

			/**
			 * Donation Goals
			 */
			'donation_goal_options' => apply_filters(
				'give_donation_goal_options',
				[
					'id'        => 'donation_goal_options',
					'title'     => __( 'Donation Goal', 'give' ),
					'icon-html' => '<i class="fas fa-bullseye"></i>',
					'fields'    => apply_filters(
						'give_forms_donation_goal_metabox_fields',
						[
							// Goals
							[
								'name'        => __( 'Donation Goal', 'give' ),
								'description' => __( 'Do you want to set a donation goal for this form?', 'give' ),
								'id'          => $prefix . 'goal_option',
								'type'        => 'radio_inline',
								'default'     => 'disabled',
								'options'     => [
									'enabled'  => __( 'Enabled', 'give' ),
									'disabled' => __( 'Disabled', 'give' ),
								],
							],

							[
								'name'        => __( 'Goal Format', 'give' ),
								'description' => __( 'Do you want to display the total amount raised based on your monetary goal or a percentage? For instance, "$500 of $1,000 raised" or "50% funded" or "1 of 5 donations". You can also display a donor-based goal, such as "100 of 1,000 donors have given".', 'give' ),
								'id'          => $prefix . 'goal_format',
								'type'        => 'donation_form_goal',
								'default'     => 'amount',
								'options'     => [
									'amount'     => __( 'Amount Raised', 'give' ),
									'percentage' => __( 'Percentage Raised', 'give' ),
									'donation'   => __( 'Number of Donations', 'give' ),
									'donors'     => __( 'Number of Donors', 'give' ),
								],
							],

							[
								'name'          => __( 'Goal Amount', 'give' ),
								'description'   => __( 'This is the monetary goal amount you want to reach for this form.', 'give' ),
								'id'            => $prefix . 'set_goal',
								'type'          => 'text_small',
								'data_type'     => 'price',
                                'default'       => give_format_decimal( [ 'amount' => '10000.00' ] ),
                                'attributes'    => [
									'placeholder' => give_format_decimal( [ 'amount' => '10000.00' ] ),
									'class'       => 'give-money-field',
								],
								'wrapper_class' => 'give-hidden',
							],
							[
								'id'         => $prefix . 'number_of_donation_goal',
								'name'       => __( 'Donation Goal', 'give' ),
								'desc'       => __( 'Set the total number of donations as a goal.', 'give' ),
								'type'       => 'number',
								'default'    => 1,
								'attributes' => [
									'placeholder' => 1,
								],
							],
							[
								'id'         => $prefix . 'number_of_donor_goal',
								'name'       => __( 'Donor Goal', 'give' ),
								'desc'       => __( 'Set the total number of donors as a goal.', 'give' ),
								'type'       => 'number',
								'default'    => 1,
								'attributes' => [
									'placeholder' => 1,
								],
							],
							[
								'name'          => __( 'Progress Bar Color', 'give' ),
								'desc'          => __( 'Customize the color of the goal progress bar.', 'give' ),
								'id'            => $prefix . 'goal_color',
								'type'          => 'colorpicker',
								'default'       => '#2bc253',
								'wrapper_class' => 'give-hidden',
							],

							[
								'name'          => __( 'Close Form', 'give' ),
								'desc'          => __( 'Do you want to close the donation forms and stop accepting donations once this goal has been met?', 'give' ),
								'id'            => $prefix . 'close_form_when_goal_achieved',
								'type'          => 'radio_inline',
								'default'       => 'disabled',
								'options'       => [
									'enabled'  => __( 'Enabled', 'give' ),
									'disabled' => __( 'Disabled', 'give' ),
								],
								'wrapper_class' => 'give-hidden',
							],
							[
								'name'          => __( 'Goal Achieved Message', 'give' ),
								'desc'          => __( 'Do you want to display a custom message when the goal is closed?', 'give' ),
								'id'            => $prefix . 'form_goal_achieved_message',
								'type'          => 'wysiwyg',
								'default'       => __( 'Thank you to all our donors, we have met our fundraising goal.', 'give' ),
								'wrapper_class' => 'give-hidden',
							],
							[
								'name'  => 'donation_goal_docs',
								'type'  => 'docs_link',
								'url'   => 'http://docs.givewp.com/form-donation-goal',
								'title' => __( 'Donation Goal', 'give' ),
							],
						],
						$post_id
					),
				]
			),

			/**
			 * Terms & Conditions
			 */
			'form_terms_options'    => apply_filters(
				'give_forms_terms_options',
				[
					'id'        => 'form_terms_options',
					'title'     => __( 'Terms & Conditions', 'give' ),
					'icon-html' => '<i class="far fa-check-square"></i>',
					'fields'    => apply_filters(
						'give_forms_terms_options_metabox_fields',
						[
							// Donation Option
							[
								'name'        => __( 'Terms and Conditions', 'give' ),
								'description' => __( 'Do you want to require the donor to accept terms prior to being able to complete their donation?', 'give' ),
								'id'          => $prefix . 'terms_option',
								'type'        => 'radio_inline',
								'options'     => apply_filters(
									'give_forms_content_options_select',
									[
										'global'   => __( 'Global Option', 'give' ),
										'enabled'  => __( 'Customize', 'give' ),
										'disabled' => __( 'Disable', 'give' ),
									]
								),
								'default'     => 'global',
							],
							[
								'id'            => $prefix . 'agree_label',
								'name'          => __( 'Agreement Label', 'give' ),
								'desc'          => __( 'The label shown next to the agree to terms check box. Add your own to customize or leave blank to use the default text placeholder.', 'give' ),
								'type'          => 'textarea',
								'attributes'    => [
									'placeholder' => __( 'Agree to Terms?', 'give' ),
									'rows'        => 1,
								],
								'wrapper_class' => 'give-hidden',
							],
							[
								'id'            => $prefix . 'agree_text',
								'name'          => __( 'Agreement Text', 'give' ),
								'desc'          => __( 'This is the actual text which the user will have to agree to in order to make a donation.', 'give' ),
								'default'       => give_get_option( 'agreement_text' ),
								'type'          => 'wysiwyg',
								'wrapper_class' => 'give-hidden',
							],
							[
								'name'  => 'terms_docs',
								'type'  => 'docs_link',
								'url'   => 'http://docs.givewp.com/form-terms',
								'title' => __( 'Terms and Conditions', 'give' ),
							],
						],
						$post_id
					),
				]
			),

            /**
             * Form Grid
             *
             * @since 2.20.0
             */
            'form_grid_options'    => apply_filters(
                'give_forms_grid_options',
                [
                    'id'        => 'form_grid_options',
                    'title'     => __( 'Form Grid', 'give' ),
                    'icon-html' => '<i class="fas fa-th-large"></i>',
                    'fields'    => [
                        [
                            'name'        => __( 'Form Grid', 'give' ),
                            'description' => __( 'These settings are used to customize how this form looks or functions when displayed as a part of a Form Grid. The default option is for donors to be redirected to the individual form page (linked above), and to have "Donate" as the default text. To change that behavior, select to customize the options.', 'give' ),
                            'id'          => $prefix . 'form_grid_option',
                            'type'        => 'radio_inline',
                            'default'     => 'default',
                            'options'     => [
                                'default' => __( 'Default options', 'give' ),
                                'custom'  => __( 'Customize', 'give' ),
                            ],
                            'attributes'  => [
                                'class' => 'give-visibility-handler',
                            ]
                        ],
                        [
                            'name'          => __( 'Redirect URL', 'give' ),
                            'description'   => __( 'The full URL of the page you want this form to redirect to when clicked on from the Form Grid. This only applies when the Form Grid uses the “Redirect” method. ', 'give' ),
                            'id'            => $prefix . 'form_grid_redirect_url',
                            'type'          => 'text-medium',
                            'attributes'    => [
                                'placeholder' => 'https://example.com/donation-form',
                                'data-field-visibility' => htmlspecialchars(json_encode([  $prefix . 'form_grid_option' => 'custom' ])),
                            ],
                            'wrapper_class' => 'give-hidden',
                        ],
                        [
                            'name'          => __( 'Donate Button Text', 'give' ),
                            'description'   => __( 'The text on the Donate Button for this form when displayed on the Form Grid. This setting only applies if the Donate Button display option is enabled in your Form Grid.', 'give' ),
                            'id'            => $prefix . 'form_grid_donate_button_text',
                            'type'          => 'text-medium',
                            'attributes'    => [
                                'placeholder' => 'Donate Here',
                                'data-field-visibility' => htmlspecialchars(json_encode([  $prefix . 'form_grid_option' => 'custom' ])),
                            ],
                            'wrapper_class' => 'give-hidden',
                        ],
                    ],
                ]
            ),
		];

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
	 * @since 1.8
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_meta_box(
			$this->get_metabox_ID(),
			$this->get_metabox_label(),
			[ $this, 'output' ],
			[ 'give_forms' ],
			'normal',
			'high'
		);

		// Show Goal Metabox only if goal is enabled.
		if ( give_is_setting_enabled( give_get_meta( give_get_admin_post_id(), '_give_goal_option', true ) ) ) {
			add_meta_box(
				'give-form-goal-stats',
				__( 'Goal Statistics', 'give' ),
				[ $this, 'output_goal' ],
				[ 'give_forms' ],
				'side',
				'low'
			);
		}

	}


	/**
	 * Enqueue scripts.
	 *
	 * @since 1.8
	 *
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
	 * @since 1.8
	 *
	 * @return string
	 */
	function get_metabox_ID() {
		return $this->metabox_id;
	}

	/**
	 * Get metabox label.
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	function get_metabox_label() {
		return $this->metabox_label;
	}


	/**
	 * Get metabox tabs.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	public function get_tabs() {
		$tabs = [];

		if ( ! empty( $this->settings ) ) {
			foreach ( $this->settings as $setting ) {
				if ( ! isset( $setting['id'] ) || ! isset( $setting['title'] ) ) {
					continue;
				}
				$tab = [
					'id'        => $setting['id'],
					'label'     => $setting['title'],
					'icon-html' => ( ! empty( $setting['icon-html'] ) ? $setting['icon-html'] : '' ),
				];

				if ( $this->has_sub_tab( $setting ) ) {
					if ( empty( $setting['sub-fields'] ) ) {
						$tab = [];
					} else {
						foreach ( $setting['sub-fields'] as $sub_fields ) {
							$tab['sub-fields'][] = [
								'id'        => $sub_fields['id'],
								'label'     => $sub_fields['title'],
								'icon-html' => ( ! empty( $sub_fields['icon-html'] ) ? $sub_fields['icon-html'] : '' ),
							];
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
	 * @since 1.8
	 *
	 * @return void
	 */
	public function output() {
		// Bailout.
		if ( $form_data_tabs = $this->get_tabs() ) :
			Template\LegacyFormSettingCompatibility::migrateExistingFormSettings();

			$active_tab = ! empty( $_GET['give_tab'] ) ? give_clean( $_GET['give_tab'] ) : 'form_template_options';
			wp_nonce_field( 'give_save_form_meta', 'give_form_meta_nonce' );

			$added_upsells_notice = false;
			?>
			<input id="give_form_active_tab" type="hidden" name="give_form_active_tab">
			<div class="give-metabox-panel-wrap">
				<ul class="give-form-data-tabs give-metabox-tabs">
					<?php foreach ( $form_data_tabs as $index => $form_data_tab ) : ?>
						<?php
						// Determine if current tab is active.
						$is_active = $active_tab === $form_data_tab['id'] ? true : false;
						?>
						<li class="<?php echo "{$form_data_tab['id']}_tab" . ( $is_active ? ' active' : '' ) . ( $this->has_sub_tab( $form_data_tab ) ? ' has-sub-fields' : '' ); ?>">
							<a href="#<?php echo $form_data_tab['id']; ?>"
							   data-tab-id="<?php echo $form_data_tab['id']; ?>">
								<?php if ( ! empty( $form_data_tab['icon-html'] ) ) : ?>
									<?php echo $form_data_tab['icon-html']; ?>
								<?php else : ?>
									<i class="fas fa-angle-right"></i>
								<?php endif; ?>
								<span class="give-label"><?php echo $form_data_tab['label']; ?></span>
							</a>
							<?php if ( $this->has_sub_tab( $form_data_tab ) ) : ?>
								<ul class="give-metabox-sub-tabs give-hidden">
									<?php foreach ( $form_data_tab['sub-fields'] as $sub_tab ) : ?>
										<li class="<?php echo "{$sub_tab['id']}_tab"; ?>">
											<a href="#<?php echo $sub_tab['id']; ?>"
											   data-tab-id="<?php echo $sub_tab['id']; ?>">
												<?php if ( ! empty( $sub_tab['icon-html'] ) ) : ?>
													<?php echo $sub_tab['icon-html']; ?>
												<?php else : ?>
													<i class="fas fa-angle-right"></i>
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

				<?php foreach ( $this->settings as $setting ) : ?>
					<?php do_action( "give_before_{$setting['id']}_settings" ); ?>
					<?php
					// Determine if current panel is active.
					$is_active = $active_tab === $setting['id'];
					?>
					<div id="<?php echo $setting['id']; ?>"
						 class="panel give_options_panel<?php echo( $is_active ? ' active' : '' ); ?>">
						<?php
						if ( ! $added_upsells_notice ) {
							echo UpsellNotice::recurringAddon();
							$added_upsells_notice = true;
						}
						?>
						<?php if ( ! empty( $setting['fields'] ) ) : ?>
							<?php foreach ( $setting['fields'] as $field ) : ?>
								<?php give_render_field( $field ); ?>
							<?php endforeach; ?>
						<?php endif; ?>
						<?php do_action( "give_post_{$setting['id']}_settings" ); ?>
					</div>
					<?php do_action( "give_after_{$setting['id']}_settings" ); ?>


					<?php if ( $this->has_sub_tab( $setting ) ) : ?>
						<?php if ( ! empty( $setting['sub-fields'] ) ) : ?>
							<?php foreach ( $setting['sub-fields'] as $index => $sub_fields ) : ?>
								<div id="<?php echo $sub_fields['id']; ?>" class="panel give_options_panel give-hidden">
									<?php if ( ! empty( $sub_fields['fields'] ) ) : ?>
										<?php foreach ( $sub_fields['fields'] as $sub_field ) : ?>
											<?php give_render_field( $sub_field ); ?>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						<?php endif; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php
		endif; // End if().
	}

	/**
	 * Output Goal meta-box settings.
	 *
	 * @param object $post Post Object.
	 *
	 * @access public
	 * @since  2.1.0
	 *
	 * @return void
	 */
	public function output_goal( $post ) {

		echo give_admin_form_goal_stats( $post->ID );

	}

	/**
	 * Check if setting field has sub tabs/fields
	 *
	 * @param array $field_setting Field Settings.
	 *
	 * @since 1.8
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
	 * @since 1.8
	 *
	 * @return array
	 */
	function cmb2_metabox_settings() {
		$all_cmb2_settings   = apply_filters( 'cmb2_meta_boxes', [] );
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
	 * @param int        $post_id Post ID.
	 * @param int|object $post    Post Object.
	 *
	 * @since 1.8
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
					! isset( $_POST[ $form_meta_key ] ) &&
					in_array( $this->get_field_type( $form_meta_key ), [ 'checkbox', 'chosen', 'multicheck' ] )
				) {
					$_POST[ $form_meta_key ] = '';
				}

				if ( isset( $_POST[ $form_meta_key ] ) ) {
					$setting_field = $this->get_setting_field( $form_meta_key );
					if ( ! empty( $setting_field['type'] ) ) {
						switch ( $setting_field['type'] ) {
							case 'textarea':
							case 'wysiwyg':
								$form_meta_value = wp_kses_post( $_POST[ $form_meta_key ] );
								break;

							case 'donation_limit':
								$form_meta_value = $_POST[ $form_meta_key ];
								break;

							case 'group':
								$form_meta_value = [];

								foreach ( $_POST[ $form_meta_key ] as $index => $group ) {

									// Do not save template input field values.
									if ( '{{row-count-placeholder}}' === $index ) {
										continue;
									}

									$group_meta_value = [];
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
								break;

							default:
								$form_meta_value = give_clean( $_POST[ $form_meta_key ] );
						}// End switch().

						/**
						 * Filter the form meta value before saving
						 *
						 * @since 1.8.9
						 */
						$form_meta_value = apply_filters(
							'give_pre_save_form_meta_value',
							$this->sanitize_form_meta( $form_meta_value, $setting_field ),
							$form_meta_key,
							$this,
							$post_id
						);

						// Range slider.
						if ( 'donation_limit' === $setting_field['type'] ) {

							// Sanitize amount for db.
							$form_meta_value = array_map( 'give_sanitize_amount_for_db', $form_meta_value );

							// Store it to form meta.
							give_update_meta( $post_id, $form_meta_key . '_minimum', $form_meta_value['minimum'] );
							give_update_meta( $post_id, $form_meta_key . '_maximum', $form_meta_value['maximum'] );
						} else {
							// Save data.
							give_update_meta( $post_id, $form_meta_key, $form_meta_value );
						}

						// Verify and delete form meta based on the form status.
						give_set_form_closed_status( $post_id );

						// Fire after saving form meta key.
						do_action( "give_save_{$form_meta_key}", $form_meta_key, $form_meta_value, $post_id, $post );
					}// End if().
				}// End if().
			}// End foreach().
		}// End if().

		// Update the goal progress for donation form.
		give_update_goal_progress( $post_id );

		// Fire action after saving form meta.
		do_action( 'give_post_process_give_forms_meta', $post_id, $post );
	}


	/**
	 * Save form template setting handler
	 *
	 * @param string $meta_key
	 * @param string $new_template
	 * @param int    $formID
	 */
	public function save_form_template_settings( $meta_key, $new_template, $formID ) {
		// Save setting for new template only if it is not empty.
		if ( ! $new_template ) {
			return;
		}

		$options = $_POST[ $new_template ];

		// Exit
		if ( empty( $options ) ) {
			return;
		}

		/* @var Template $template */
		$template = Give()->templates->getTemplate( $new_template );

		// If selected theme is not registered then do not save it's options.
		if ( null === $template ) {
			return;
		}

		/* @var Template\Options $templateOptions */
		$templateOptions = $template->getOptions();
		$saveOptions     = FormTemplateUtils::getOptions( $formID );

		/* @var Section $group */
		foreach ( $templateOptions->sections as $group ) {
			/* @var Fields $field */
			foreach ( $group->fields as $field ) {
				if ( ! isset( $options[ $group->id ][ $field->id ] ) ) {
					continue;
				}

				// Initialize $value for every field to prevent value type conflict.
				$value = null;

				switch ( $field->type ) {
					case 'textarea':
					case 'wysiwyg':
						$value = wp_kses_post( $options[ $group->id ][ $field->id ] );
						break;

					case 'donation_limit':
						$value = $options[ $group->id ][ $field->id ];
						break;

					case 'group':
						/* @var \Give\FormAPI\Form\Group $field */
						foreach ( $options[ $group->id ][ $field->id ] as $index => $subFields ) {

							// Do not save template input field values.
							if ( '{{row-count-placeholder}}' === $index ) {
								continue;
							}

							$group_of_values = [];

							foreach ( $subFields as $field_id => $field_value ) {
								switch ( $field->getFieldArguments( $field_id )['type'] ) {
									case 'wysiwyg':
										$group_of_values[ $field_id ] = wp_kses_post( $field_value );
										break;

									default:
										$group_of_values[ $field_id ] = give_clean( $field_value );
								}
							}

							if ( ! empty( $group_of_values ) ) {
								$value[ $index ] = $group_of_values;
							}
						}

						// Arrange repeater field keys in order.
						$value = $value ? array_values( $value ) : [];
						break;

					default:
						$value = give_clean( $options[ $group->id ][ $field->id ] );
				}// End switch().

				$saveOptions[ $group->id ][ $field->id ] = $value;
			}
		}

		FormTemplateUtils::saveOptions( $formID, $saveOptions );
	}


	/**
	 * Get field ID.
	 *
	 * @param array $field Array of Fields.
	 *
	 * @since 1.8
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
	 * @param array $setting Array of settings.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_fields_id( $setting ) {
		$meta_keys = [];

		if (
			! empty( $setting )
			&& array_key_exists( 'fields', $setting )
			&& ! empty( $setting['fields'] )
		) {
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
	 * @param array $setting Array of settings.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_sub_fields_id( $setting ) {
		$meta_keys = [];

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
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_meta_keys_from_settings() {
		$meta_keys = [];

		foreach ( $this->settings as $setting ) {
			$meta_key = $this->get_fields_id( $setting );

			if ( $this->has_sub_tab( $setting ) ) {
				$meta_key = array_merge( $meta_key, $this->get_sub_fields_id( $setting ) );
			}

			$meta_keys = array_merge( $meta_keys, $meta_key );
		}

		return $meta_keys;
	}


	/**
	 * Get field type.
	 *
	 * @param string $field_id Field ID.
	 * @param string $group_id Field Group ID.
	 *
	 * @since 1.8
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
	 * @param array  $setting  Settings array.
	 * @param string $field_id Field ID.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_field( $setting, $field_id ) {
		$setting_field = [];

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
	 * @param array  $setting  Settings array.
	 * @param string $field_id Field ID.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	private function get_sub_field( $setting, $field_id ) {
		$setting_field = [];

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
	 * @param string $field_id Field ID.
	 * @param string $group_id Get sub field from group.
	 *
	 * @since 1.8
	 *
	 * @return array
	 */
	function get_setting_field( $field_id, $group_id = '' ) {
		$setting_field = [];

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
	 * @param array $settings List of form settings.
	 *
	 * @since 1.8
	 *
	 * @return mixed
	 */
	function add_offline_donations_setting_tab( $settings ) {
		if ( give_is_gateway_active( 'offline' ) ) {
			$settings['offline_donations_options'] = apply_filters(
				'give_forms_offline_donations_options',
				[
					'id'        => 'offline_donations_options',
					'title'     => __( 'Offline Donations', 'give' ),
					'icon-html' => '<i class="fas fa-wallet"></i>',
					'fields'    => apply_filters( 'give_forms_offline_donations_metabox_fields', [] ),
				]
			);
		}

		return $settings;
	}


	/**
	 * Sanitize form meta values before saving.
	 *
	 * @param mixed $meta_value    Meta Value for sanitizing before saving.
	 * @param array $setting_field Setting Field.
	 *
	 * @since  1.8.9
	 * @access public
	 *
	 * @return mixed
	 */
	function sanitize_form_meta( $meta_value, $setting_field ) {
		switch ( $setting_field['type'] ) {
			case 'group':
				if ( ! empty( $setting_field['fields'] ) ) {
					foreach ( $setting_field['fields'] as $field ) {
						if ( empty( $field['data_type'] ) || 'price' !== $field['data_type'] ) {
							continue;
						}

						foreach ( $meta_value as $index => $meta_data ) {
							if ( ! isset( $meta_value[ $index ][ $field['id'] ] ) ) {
								continue;
							}

							$meta_value[ $index ][ $field['id'] ] = ! empty( $meta_value[ $index ][ $field['id'] ] ) ?
								give_sanitize_amount_for_db( $meta_value[ $index ][ $field['id'] ] ) :
								( ( '_give_amount' === $field['id'] && empty( $field_value ) ) ?
									give_sanitize_amount_for_db( '1.00' ) :
									0 );
						}
					}
				}
				break;

			default:
				if ( ! empty( $setting_field['data_type'] ) && 'price' === $setting_field['data_type'] ) {
					$meta_value = $meta_value ?
						give_sanitize_amount_for_db( $meta_value ) :
						( in_array(
							$setting_field['id'],
							[
								'_give_set_price',
								'_give_custom_amount_minimum',
								'_give_set_goal',
							]
						) ?
							give_sanitize_amount_for_db( '1.00' ) :
							0 );
				}
		}

		return $meta_value;
	}

	/**
	 * Maintain the active tab after save.
	 *
	 * @param string $location The destination URL.
	 * @param int    $post_id  The post ID.
	 *
	 * @since  1.8.13
	 * @access public
	 *
	 * @return string The URL after redirect.
	 */
	public function maintain_active_tab( $location, $post_id ) {
		if (
			'give_forms' === get_post_type( $post_id ) &&
			! empty( $_POST['give_form_active_tab'] )
		) {
			$location = esc_url_raw( add_query_arg( 'give_tab', give_clean( $_POST['give_form_active_tab'] ), $location ) ); }

		return $location;
	}
}

new Give_MetaBox_Form_Data();


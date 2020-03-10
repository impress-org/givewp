<?php
return [
	'id'      => 'legacy',
	'name'    => __( 'Legacy - Standard Form', 'give' ),
	'image'   => '',
	'options' => [
		'display_settings' => [
			'name'   => __( 'Form Display', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'name'    => __( 'Display Options', 'give' ),
					'desc'    => sprintf( __( 'How would you like to display donation information for this form?', 'give' ), '#' ),
					'id'      => 'legacy_payment_display',
					'type'    => 'radio_inline',
					'options' => [
						'onpage' => __( 'All Fields', 'give' ),
						'modal'  => __( 'Modal', 'give' ),
						'reveal' => __( 'Reveal', 'give' ),
						'button' => __( 'Button', 'give' ),
					],
					'default' => 'onpage',
				],
				[
					'id'            => 'legacy_reveal_label',
					'name'          => __( 'Continue Button', 'give' ),
					'desc'          => __( 'The button label for displaying the additional payment fields.', 'give' ),
					'type'          => 'text_small',
					'attributes'    => [
						'placeholder' => __( 'Donate Now', 'give' ),
					],
					'wrapper_class' => 'give-hidden',
				],
				[
					'id'         => 'legacy_checkout_label',
					'name'       => __( 'Submit Button', 'give' ),
					'desc'       => __( 'The button label for completing a donation.', 'give' ),
					'type'       => 'text_small',
					'attributes' => [
						'placeholder' => __( 'Donate Now', 'give' ),
					],
				],
				[
					'name'    => __( 'Floating Labels', 'give' ),
					/* translators: %s: forms http://docs.givewp.com/form-floating-labels */
					'desc'    => sprintf( __( 'Select the <a href="%s" target="_blank">floating labels</a> setting for this GiveWP form. Be aware that if you have the "Disable CSS" option enabled, you will need to style the floating labels yourself.', 'give' ), esc_url( 'http://docs.givewp.com/form-floating-labels' ) ),
					'id'      => 'legacy_form_floating_labels',
					'type'    => 'radio_inline',
					'options' => [
						'global'   => __( 'Global Option', 'give' ),
						'enabled'  => __( 'Enabled', 'give' ),
						'disabled' => __( 'Disabled', 'give' ),
					],
					'default' => 'global',
				],
				[
					'name'        => __( 'Display Content', 'give' ),
					'description' => __( 'Do you want to add custom content to this form?', 'give' ),
					'id'          => 'legacy_display_content',
					'type'        => 'radio_inline',
					'options'     => array(
						'enabled'  => __( 'Enabled', 'give' ),
						'disabled' => __( 'Disabled', 'give' ),
					),
					'default'     => 'disabled',
				],
				[
					'name'          => __( 'Content Placement', 'give' ),
					'description'   => __( 'This option controls where the content appears within the donation form.', 'give' ),
					'id'            => 'legacy_content_placement',
					'type'          => 'radio_inline',
					'options'       => apply_filters(
						'give_forms_content_options_select',
						[
							'give_pre_form'  => __( 'Above fields', 'give' ),
							'give_post_form' => __( 'Below fields', 'give' ),
						]
					),
					'default'       => 'give_pre_form',
					'wrapper_class' => 'give-hidden',
				],
				[
					'name'          => __( 'Content', 'give' ),
					'description'   => __( 'This content will display on the single give form page.', 'give' ),
					'id'            => 'legacy_form_content',
					'type'          => 'wysiwyg',
					'wrapper_class' => 'give-hidden',
				],
			],
		],
	],
];

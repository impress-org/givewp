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
					'name' => __( 'Default Gateway', 'give' ),
					'desc' => __( 'By default, the gateway for this form will inherit the global default gateway (set under GiveWP > Settings > Payment Gateways). This option allows you to customize the default gateway for this form only.', 'give' ),
					'id'   => 'legacy_default_gateway',
					'type' => 'default_gateway',
				],
				[
					'name'    => __( 'Name Title Prefix', 'give' ),
					'desc'    => __( 'Do you want to add a name title prefix dropdown field before the donor\'s first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for donor to choose from.', 'give' ),
					'id'      => 'legacy_name_title_prefix',
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
					'name'          => __( 'Title Prefixes', 'give' ),
					'desc'          => __( 'Add or remove salutations from the dropdown using the field above.', 'give' ),
					'id'            => 'legacy_title_prefixes',
					'type'          => 'chosen',
					'data_type'     => 'multiselect',
					'style'         => 'width: 100%',
					'wrapper_class' => 'give-hidden give-title-prefixes-wrap',
					'options'       => give_get_default_title_prefixes(),
				],
				[
					'name'    => __( 'Company Donations', 'give' ),
					'desc'    => __( 'Do you want a Company field to appear after First Name and Last Name?', 'give' ),
					'id'      => 'legacy_company_field',
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
					'name'    => __( 'Anonymous Donations', 'give' ),
					'desc'    => __( 'Do you want to provide donors the ability mark themselves anonymous while giving. This will prevent their information from appearing publicly on your website but you will still receive their information for your records in the admin panel.', 'give' ),
					'id'      => "'legacy_anonymous_donation",
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
					'id'      => "'legacy_donor_comment",
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
					'id'      => 'legacy_logged_in_only',
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
					'id'      => 'legacy_show_register_form',
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
			],
		],
	],
];

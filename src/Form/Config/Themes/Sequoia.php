<?php
return [
	'id'      => 'sequoia',
	'name'    => __( 'Sequoia - Multi-Step Form', 'give' ),
	'image'   => '',
	'options' => [
		'introduction'        => [
			'name'   => __( 'Introduction', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'intro_headline',
					'name'       => __( 'Headline', 'give' ),
					'desc'       => __( 'Do you want to customize the headline for this form? We recommend keeping it to no more than 8 words as a best practive. If no title is provided the fallback will be your form’s post title.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'Campaign Heading', 'give' ),
					],
				],
				[
					'id'         => 'intro_description',
					'name'       => __( 'Description', 'give' ),
					'desc'       => __( 'Do you want to customize the description for this form? The description displays below the headline. We recommend keeping it to 1-2 short sentences. If no description is provided the fallback will be your form’s excerpt.', 'give' ),
					'type'       => 'textarea',
					'attributes' => [
						'placeholder' => __( 'Help provide education, care, and community development. It couldn’t happen without you.', 'give' ),
					],
				],
				[
					'id'   => 'intro_image',
					'name' => __( 'Image', 'give' ),
					'desc' => __( 'Upload an eye-catching image that reflects your cause. The image is required and if none is provided the featured image will be a the fallback. If none is set you will see a placeholder image displayed on the form. For best results use an image that’s 600x400 pixels.', 'give' ),
					'type' => 'upload',
				],
				[
					'id'   => 'intro_primary_color',
					'name' => __( 'Primary Color', 'give' ),
					'desc' => __( 'The primary color is used through the Form Theme for various elements including buttons, line breaks, and focus and hover elements. Set a color that reflects your brand or main featured image for best results.', 'give' ),
					'type' => 'color',
				],
			],
		],
		'donation_amount'     => [
			'name'   => __( 'Donation Amount', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'sequoia_reveal_label',
					'name'       => __( 'Continue Button', 'give' ),
					'desc'       => __( 'The button label for displaying the additional payment fields.', 'give' ),
					'type'       => 'text_small',
					'attributes' => [
						'placeholder' => __( 'Donate Now', 'give' ),
					],
				],
			],
		],
		'payment_information' => [
			'name'   => __( 'Payment Information', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'sequoia_checkout_label',
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
					'id'   => 'sequoia_default_gateway',
					'type' => 'default_gateway',
				],
				[
					'name'    => __( 'Name Title Prefix', 'give' ),
					'desc'    => __( 'Do you want to add a name title prefix dropdown field before the donor\'s first name field? This will display a dropdown with options such as Mrs, Miss, Ms, Sir, and Dr for donor to choose from.', 'give' ),
					'id'      => 'sequoia_name_title_prefix',
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
					'id'            => 'sequoia_title_prefixes',
					'type'          => 'chosen',
					'data_type'     => 'multiselect',
					'style'         => 'width: 100%',
					'wrapper_class' => 'give-hidden give-title-prefixes-wrap',
					'options'       => give_get_default_title_prefixes(),
				],
				[
					'name'    => __( 'Company Donations', 'give' ),
					'desc'    => __( 'Do you want a Company field to appear after First Name and Last Name?', 'give' ),
					'id'      => 'sequoia_company_field',
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
					'id'      => "'sequoia_anonymous_donation",
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
					'id'      => "'sequoia_donor_comment",
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
					'id'      => 'sequoia_logged_in_only',
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
					'id'      => 'sequoia_show_register_form',
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
					'id'      => 'sequoia_form_floating_labels',
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
		'thank-you'           => [
			'name'   => __( 'Thank You', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'thank_you_headline',
					'name'       => __( 'Headline', 'give' ),
					'desc'       => __( 'This message should be short and sweet. Make the donor feel good about their donation so they continue to give in the future. This text is required and you may use any of the available template tags within this message.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'A great big thank you!', 'give' ),
					],
				],
				[
					'id'         => 'thank_you_description',
					'name'       => __( 'Description', 'give' ),
					'desc'       => __( 'The description is displayed directly below the main headline and should be 1-2 sentences for best performance. You may use any of the available template tags within this message.', 'give' ),
					'type'       => 'textarea',
					'attributes' => [
						'placeholder' => __( '{name}, you contribution means a lot and will be put to good use making a difference. We’ve sent your donation receipt to {donor_email}. ', 'give' ),
					],
				],
				[
					'id'   => 'thank_you_image',
					'name' => __( 'Image', 'give' ),
					'desc' => __( 'Upload an eye-catching image that reflects your cause. The image is required and if none is provided the featured image will be a the fallback. If none is set you will see a placeholder image displayed on the form. For best results use an image that’s 600x400 pixels.', 'give' ),
					'type' => 'upload',
				],
			],
		],
	],
];

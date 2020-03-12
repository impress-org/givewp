<?php
return [
	'id'      => 'sequoia',
	'name'    => __( 'Sequoia - Multi-Step Form', 'give' ),
	'image'   => 'https://images.unsplash.com/photo-1448387473223-5c37445527e7?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=300&q=100',
	'options' => [
		'introduction'        => [
			'name'   => __( 'Introduction', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'headline',
					'name'       => __( 'Headline', 'give' ),
					'desc'       => __( 'Do you want to customize the headline for this form? We recommend keeping it to no more than 8 words as a best practive. If no title is provided the fallback will be your form’s post title.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'Campaign Heading', 'give' ),
					],
				],
				[
					'id'         => 'description',
					'name'       => __( 'Description', 'give' ),
					'desc'       => __( 'Do you want to customize the description for this form? The description displays below the headline. We recommend keeping it to 1-2 short sentences. If no description is provided the fallback will be your form’s excerpt.', 'give' ),
					'type'       => 'textarea_small',
					'attributes' => [
						'placeholder' => __( 'Help provide education, care, and community development. It couldn’t happen without you.', 'give' ),
					],
				],
				[
					'id'         => 'image',
					'name'       => __( 'Image', 'give' ),
					'desc'       => __( 'Upload an eye-catching image that reflects your cause. The image is required and if none is provided the featured image will be a the fallback. If none is set you will see a placeholder image displayed on the form. For best results use an image that’s 600x400 pixels.', 'give' ),
					'type'       => 'file',
					'query_args' => [
						'type' => [
							'image/gif',
							'image/jpeg',
							'image/png',
						],
					],
				],
				[
					'id'   => 'primary_color',
					'name' => __( 'Primary Color', 'give' ),
					'desc' => __( 'The primary color is used through the Form Theme for various elements including buttons, line breaks, and focus and hover elements. Set a color that reflects your brand or main featured image for best results.', 'give' ),
					'type' => 'colorpicker',
				],
			],
		],
		'donation_amount'     => [
			'name'   => __( 'Donation Amount', 'give' ),
			'desc'   => __( 'Step descruiption will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'reveal_label',
					'name'       => __( 'Continue Button', 'give' ),
					'desc'       => __( 'The button label for displaying the additional payment fields.', 'give' ),
					'type'       => 'text_medium',
					'attributes' => [
						'placeholder' => __( 'Add Payment Information', 'give' ),
					],
				],
			],
		],
		'payment_information' => [
			'name'   => __( 'Payment Information', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'checkout_label',
					'name'       => __( 'Submit Button', 'give' ),
					'desc'       => __( 'The button label for completing a donation.', 'give' ),
					'type'       => 'text_medium',
					'attributes' => [
						'placeholder' => __( 'Donate Now', 'give' ),
					],
				],
			],
		],
		'thank-you'           => [
			'name'   => __( 'Thank You', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'headline',
					'name'       => __( 'Headline', 'give' ),
					'desc'       => __( 'This message should be short and sweet. Make the donor feel good about their donation so they continue to give in the future. This text is required and you may use any of the available template tags within this message.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'A great big thank you!', 'give' ),
					],
				],
				[
					'id'         => 'description',
					'name'       => __( 'Description', 'give' ),
					'desc'       => __( 'The description is displayed directly below the main headline and should be 1-2 sentences for best performance. You may use any of the available template tags within this message.', 'give' ),
					'type'       => 'textarea',
					'attributes' => [
						'placeholder' => __( '{name}, you contribution means a lot and will be put to good use making a difference. We’ve sent your donation receipt to {donor_email}. ', 'give' ),
					],
				],
				[
					'id'         => 'image',
					'name'       => __( 'Image', 'give' ),
					'desc'       => __( 'Upload an eye-catching image that reflects your cause. The image is required and if none is provided the featured image will be a the fallback. If none is set you will see a placeholder image displayed on the form. For best results use an image that’s 600x400 pixels.', 'give' ),
					'type'       => 'file',
					'query_args' => [
						'type' => [
							'image/gif',
							'image/jpeg',
							'image/png',
						],
					],
				],
			],
		],
	],
];

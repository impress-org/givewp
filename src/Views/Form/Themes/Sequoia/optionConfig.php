<?php
global $post;

// Setup dynamic defaults
$introHeadline    = $post->post_title ? $post->post_title : __( 'Campaign Heading', 'give' );
$introDescription = $post->post_excerpt ? $post->post_excerpt : __( 'Help provide education, care, and community development. It couldn’t happen without you.', 'give' );

return [
	'introduction'        => [
		'name'   => __( 'Introduction', 'give' ),
		'desc'   => __( 'Step description will show up here if any', 'give' ),
		'fields' => [
			[
				'name'    => __( 'Include Introduction', 'give' ),
				'desc'    => sprintf( __( 'Should this form include an introduction section?', 'give' ), '#' ),
				'id'      => 'enabled',
				'type'    => 'radio_inline',
				'options' => [
					'enabled'  => __( 'Enabled', 'give' ),
					'disabled' => __( 'Disabled', 'give' ),
				],
				'default' => 'enabled',
			],
			[
				'id'         => 'headline',
				'name'       => __( 'Headline', 'give' ),
				'desc'       => __( 'Do you want to customize the headline for this form? We recommend keeping it to no more than 8 words as a best practive. If no title is provided the fallback will be your form’s post title.', 'give' ),
				'type'       => 'text',
				'attributes' => [
					'placeholder' => $introHeadline,
				],
				'default'    => $introHeadline,
			],
			[
				'id'         => 'description',
				'name'       => __( 'Description', 'give' ),
				'desc'       => __( 'Do you want to customize the description for this form? The description displays below the headline. We recommend keeping it to 1-2 short sentences. If no description is provided the fallback will be your form’s excerpt.', 'give' ),
				'type'       => 'textarea',
				'attributes' => [
					'placeholder' => $introDescription,
				],
				'default'    => $introDescription,
			],
			[
				'id'   => 'image',
				'name' => __( 'Image', 'give' ),
				'desc' => __( 'Upload an eye-catching image that reflects your cause. The image is required and if none is provided the featured image will be a the fallback. If none is set you will see a placeholder image displayed on the form. For best results use an image that’s 600x400 pixels.', 'give' ),
				'type' => 'file',
			],
			[
				'id'      => 'primary_color',
				'name'    => __( 'Primary Color', 'give' ),
				'desc'    => __( 'The primary color is used through the Form Theme for various elements including buttons, line breaks, and focus and hover elements. Set a color that reflects your brand or main featured image for best results.', 'give' ),
				'type'    => 'colorpicker',
				'default' => '#28C77B',
			],
			[
				'id'         => 'donate_label',
				'name'       => __( 'Donate Button', 'give' ),
				'desc'       => __( 'The button label for displaying the additional payment fields.', 'give' ),
				'type'       => 'text_medium',
				'attributes' => [
					'placeholder' => __( 'Donate Now', 'give' ),
				],
				'default'    => __( 'Donate Now', 'give' ),
			],
		],
	],
	'payment_amount'      => [
		'name'   => __( 'Payment Amount', 'give' ),
		'desc'   => __( 'Step description will show up here if any', 'give' ),
		'fields' => [
			[
				'id'         => 'next_label',
				'name'       => __( 'Continue Button', 'give' ),
				'desc'       => __( 'The button label for displaying the additional payment fields.', 'give' ),
				'type'       => 'text_medium',
				'attributes' => [
					'placeholder' => __( 'Continue', 'give' ),
				],
				'default'    => __( 'Continue', 'give' ),
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
				'default'    => __( 'Donate Now', 'give' ),
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
				'default'    => __( 'A great big thank you!', 'give' ),
			],
			[
				'id'         => 'description',
				'name'       => __( 'Description', 'give' ),
				'desc'       => __( 'The description is displayed directly below the main headline and should be 1-2 sentences for best performance. You may use any of the available template tags within this message.', 'give' ),
				'type'       => 'textarea',
				'attributes' => [
					'placeholder' => __( '{name}, you contribution means a lot and will be put to good use making a difference. We’ve sent your donation receipt to {donor_email}. ', 'give' ),
				],
				'default'    => __( '{name}, you contribution means a lot and will be put to good use making a difference. We’ve sent your donation receipt to {donor_email}. ', 'give' ),
			],
			[
				'id'   => 'image',
				'name' => __( 'Image', 'give' ),
				'desc' => __( 'Upload an eye-catching image that reflects your cause. The image is required and if none is provided the featured image will be a the fallback. If none is set you will see a placeholder image displayed on the form. For best results use an image that’s 600x400 pixels.', 'give' ),
				'type' => 'file',
			],
		],
	],
];

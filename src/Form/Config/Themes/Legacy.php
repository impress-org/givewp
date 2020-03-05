<?php
return [
	'id'      => 'legacy',
	'name'    => __( 'Legacy - Standard Form', 'give' ),
	'image'   => '',
	'options' => [
		'introduction' => [
			'name'   => __( 'Introduction', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'heading',
					'name'       => __( 'Heading', 'give' ),
					'desc'       => __( 'Set campaign heading.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'Campaign Heading', 'give' ),
					],
				],
				[
					'id'         => 'subheading',
					'name'       => __( 'Sub Heading', 'give' ),
					'desc'       => __( 'Set campaign sub heading.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'Campaign Sub Heading', 'give' ),
					],
				],
			],
		],
		'thank-you'    => [
			'name'   => __( 'Thank You', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				[
					'id'         => 'heading',
					'name'       => __( 'Heading', 'give' ),
					'desc'       => __( 'Set campaign heading.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'Campaign Heading', 'give' ),
					],
				],
				[
					'id'         => 'subheading',
					'name'       => __( 'Sub Heading', 'give' ),
					'desc'       => __( 'Set campaign sub heading.', 'give' ),
					'type'       => 'text',
					'attributes' => [
						'placeholder' => __( 'Campaign Sub Heading', 'give' ),
					],
				],
			],
		],
	],
];

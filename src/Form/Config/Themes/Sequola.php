<?php
return [
	'id'      => 'sequola',
	'name'    => __( 'Sequola - Multi-Step Form', 'give' ),
	'image'   => '',
	'options' => [
		'introduction' => [
			'name'   => __( 'Introduction', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				array(
					'id'         => 'heading',
					'name'       => __( 'Heading', 'give' ),
					'desc'       => __( 'Set campaign heading.', 'give' ),
					'type'       => 'text',
					'attributes' => array(
						'placeholder' => __( 'Campaign Heading', 'give' ),
					),
				),
				array(
					'id'         => 'subheading',
					'name'       => __( 'Sub Heading', 'give' ),
					'desc'       => __( 'Set campaign sub heading.', 'give' ),
					'type'       => 'text',
					'attributes' => array(
						'placeholder' => __( 'Campaign Sub Heading', 'give' ),
					),
				),
				array(
					'id'            =>  'emails',
					'type'          => 'group',
					'options'       => array(
						'add_button'    => __( 'Add Email', 'give' ),
						'header_title'  => __( 'Admin Email', 'give' ),
						'remove_button' => '<span class="dashicons dashicons-no"></span>',
					),
					// Fields array works the same, except id's only need to be unique for this group.
					// Prefix is not needed.
					'fields'        => array(
						array(
							'name' => __( 'ID', 'give' ),
							'id'   => 'id',
							'type' => 'text',
						)
					)
				)
			],
		],
		'thank-you'    => [
			'name'   => __( 'Thank You', 'give' ),
			'desc'   => __( 'Step description will show up here if any', 'give' ),
			'fields' => [
				array(
					'id'         => 'heading',
					'name'       => __( 'Heading', 'give' ),
					'desc'       => __( 'Set campaign heading.', 'give' ),
					'type'       => 'text',
					'attributes' => array(
						'placeholder' => __( 'Campaign Heading', 'give' ),
					),
				),
				array(
					'id'         => 'subheading',
					'name'       => __( 'Sub Heading', 'give' ),
					'desc'       => __( 'Set campaign sub heading.', 'give' ),
					'type'       => 'text',
					'attributes' => array(
						'placeholder' => __( 'Campaign Sub Heading', 'give' ),
					),
				),
			],
		],
	],
];

<?php

/**
 * @link https://developer.wordpress.org/reference/functions/register_post_type/
 */

return [

	/**
	 * @var array An array of labels for this post type. If not set, post labels are inherited for non-hierarchical types and page labels for hierarchical ones.
	 *
	 * @link https://developer.wordpress.org/reference/functions/get_post_type_labels/
	 */
	'labels'        => [
		'name'          => __( 'Campaigns', 'give' ),
		'singular_name' => __( 'Campaign', 'give' ),
	],

	/**
	 * @var bool|string Where to show the post type in the admin menu. If a string of an existing top level menu, the post type will be placed as a sub-menu of that.
	 */
	'show_in_menu'  => 'edit.php?post_type=give_forms',

	/**
	 * @var bool Whether a post type is intended for use publicly either via the admin interface or by front-end users.
	 */
	'public'        => false,

	/**
	 * @var bool Whether to generate a default UI for managing this post type in the admin.
	 */
	'show_ui'       => true,

	/**
	 * @var bool|string Whether there should be post type archives, or if a string, the archive slug to use.
	 */
	'has_archive'   => true,

	/**
	 * @var boolean (optional) Whether to expose this post type in the REST API.
	 *
	 * @note Must be true to enable the Gutenberg editor.
	 */
	'show_in_rest'  => true, // Set this to true for the post type to be available in the block editor.

	/**
	 * (array/boolean) (optional) An alias for calling add_post_type_support() directly. As of 3.5, boolean false can be passed as value instead of an array to prevent default (title and editor) behavior.
	 */
	'supports'      => [
		'title',
		'editor',
		'thumbnail',
	],

	/**
	 * @var array A block template is defined as a list of block items. Such blocks can have predefined attributes, placeholder content, and be static or dynamic.
	 * Block templates allow to specify a default initial state for an editor session.
	 *
	 * @note Container blocks like the columns blocks also support templates. This is achieved by assigning a nested template to the block.
	 *
	 * @link https://developer.wordpress.org/block-editor/developers/block-api/block-templates/
	 */
	'template'      => [
		[
			'give/campaign-preview',
			[],
			[
				[
					'give/campaign-featured-image',
				],
				[
					'core/heading',
					[
						'placeholder' => 'Add a heading',
					],
				],
				[
					'core/paragraph',
					[
						'placeholder' => 'Description',
						'content'     => 'The description of the campaign goes here to give donors a brief introduction to the cause in case they are unfamiliar with the organization or their fundraising goals.',
					],
				],
				[
					'core/buttons',
					[ 'align' => 'left' ],
					[
						[
							'core/button',
							[
								'text'  => 'Donate Now',
								'style' => [
									'color' => [
										'text'       => '#ffffff',
										'background' => '#66bb6a',
									],
								],
							],
						],
						[
							'core/button',
							[
								'text'      => 'Share',
								'className' => 'is-style-outline',
								'style'     => [
									'color' => [
										'text'       => '#66bb6a',
										'background' => '#ffffff',
									],
								],
							],
						],
					],
				],
				[
					'give/campaign-progress-bar',
					[
						'percent' => '45',
					],
				],
			],
		],
	],

	/**
	 * @var string Sometimes the intention might be to lock the template on the UI so that the blocks presented cannot be manipulated. This is achieved with a template_lock property.
	 *
	 * @link https://developer.wordpress.org/block-editor/developers/block-api/block-templates/#locking
	 */
	'template_lock' => 'all',
];

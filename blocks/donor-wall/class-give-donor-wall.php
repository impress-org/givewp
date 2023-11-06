<?php
/**
 * Give Donor Wall Block Class
 *
 * @package     Give
 * @subpackage  Classes/Blocks
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donor_Wall_Block Class.
 *
 * This class handles donation forms block.
 *
 * @since 2.3.0
 */
class Give_Donor_Wall_Block {
	/**
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_Donor_Wall_Block
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since
	 * @access public
	 * @return Give_Donor_Wall_Block
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * Class Constructor
	 *
	 * Set up the Give Donation Grid Block class.
	 *
	 * @since  2.3.0
	 * @access private
	 */
	private function init() {
		add_action( 'init', [ $this, 'register_block' ], 999 );
	}

	/**
	 * Register block
	 *
	 * @access public
	 */
	public function register_block() {
		// Bailout.
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		// Register block.
		register_block_type(
			'give/donor-wall',
			[
				'render_callback' => [ $this, 'render_block' ],
				'attributes'      => [
					'donorsPerPage'     => [
						'type'    => 'string',
						'default' => '12',
					],
					'formID'            => [
						'type'    => 'array',
						'default' => [],
					],
					'ids'               => [
						'type'    => 'array',
						'default' => [],
					],
                    'categories'        => [
                        'type'    => 'array',
                        'default' => [],
                    ],
                    'tags'              => [
                        'type'    => 'array',
                        'default' => [],
                    ],
					'orderBy'           => [
						'type'    => 'string',
						'default' => 'post_date',
					],
					'order'             => [
						'type'    => 'string',
						'default' => 'DESC',
					],
					'paged'             => [
						'type'    => 'string',
						'default' => '1',
					],
					'columns'           => [
						'type'    => 'string',
						'default' => '3',
					],
					'showAvatar'          => [
						'type'    => 'boolean',
						'default' => true,
					],
					'showName'            => [
						'type'    => 'boolean',
						'default' => true,
					],
					'showCompanyName'     => [
						'type'    => 'boolean',
						'default' => false,
					],
                    'showForm'            => [
						'type'    => 'boolean',
						'default' => true,
					],
					'showTotal'           => [
						'type'    => 'boolean',
						'default' => true,
					],
					'showComments'        => [
						'type'    => 'boolean',
						'default' => true,
					],
					'showAnonymous'       => [
						'type'    => 'boolean',
						'default' => true,
					],
					'onlyComments'        => [
						'type'    => 'boolean',
						'default' => false,
					],
					'commentLength'      => [
						'type'    => 'string',
						'default' => '80',
					],
					'readMoreText'       => [
						'type'    => 'string',
						'default' => __( 'Read more', 'give' ),
					],
					'loadMoreText'       => [
						'type'    => 'string',
						'default' => __( 'Load more', 'give' ),
					],
					'avatarSize'         => [
						'type'    => 'string',
						'default' => '75',
					],
                    'toggleOptions'      => [
                        'type'    => 'string',
                        'default' => 'Donor info',
                    ],
                    'filterOptions'      => [
                        'type'    => 'string',
                        'default' => 'ids',
                    ],
                    'color'              => [
                        'type'    => 'string',
                        'default' => '#219653',
                    ],
                    'showTributes'        => [
                        'type'    => 'boolean',
                        'default' => false,
                    ],
                    'showTimestamp'       => [
                        'type'    => 'boolean',
                        'default' => true,
                    ],
				],
			]
		);
	}

	/**
	 * Block render callback
     *
     * @since 3.1.0 Use static function on array_map callback to pass the id as reference for _give_redirect_form_id to prevent warnings on PHP 8.0.1 or plus
	 *
	 * @param array $attributes Block parameters.
	 *
	 * @access public
	 * @return string;
	 */
	public function render_block( $attributes ) {
		$avatarSize = absint( $attributes['avatarSize'] );

		$parameters = [
			'donors_per_page'   => absint( $attributes['donorsPerPage'] ),
			'form_id'           => implode(',',
                array_map(
                    static function ($id) {
                        _give_redirect_form_id($id);

                        return $id;
                    },
                    $this->getAsArray($attributes['formID'])
                )
            ),
			'ids'               => implode(',', $this->getAsArray($attributes['ids'] ) ),
            'cats'              => implode(',', $this->getAsArray($attributes['categories'] ) ),
            'tags'              => implode(',', $this->getAsArray($attributes['tags'] ) ),
			'orderby'           => $attributes['orderBy'],
			'order'             => $attributes['order'],
			'pages'             => absint( $attributes['paged'] ),
			'columns'           => $attributes['columns'],
			'show_avatar'       => $attributes['showAvatar'],
			'show_name'         => $attributes['showName'],
			'show_company_name' => $attributes['showCompanyName'],
			'show_form'         => $attributes['showForm'],
			'show_total'        => $attributes['showTotal'],
			'show_comments'     => $attributes['showComments'],
            'show_tributes'     => $attributes['showTributes'],
            'anonymous'         => $attributes['showAnonymous'],
			'comment_length'    => absint( $attributes['commentLength'] ),
			'only_comments'     => $attributes['onlyComments'],
			'readmore_text'     => $attributes['readMoreText'],
			'loadmore_text'     => $attributes['loadMoreText'],
			'avatar_size'       => $avatarSize ?: 75,
            'color'             => $attributes['color'],
            'show_time'         => $attributes['showTimestamp'],
		];

		$html = Give_Donor_Wall::get_instance()->render_shortcode( $parameters );
		$html = ! empty( $html ) ? $html : $this->blank_slate();

		return $html;
	}


    /**
     * @since 2.25.0
     *
     * @param string|array $value
     * @return array
     */
    private function getAsArray($value) {
        if ( is_array($value) ) {
            return $value;
        }

        // Backward compatibility
        if (strpos($value, ',')) {
            return explode(',', $value);
        }

        return [$value];
    }

	/**
	 * Return formatted notice when shortcode returns an empty string
	 *
	 * @since 2.4.0
	 *
	 * @return string
	 */
	private function blank_slate() {
		if ( ! defined( 'REST_REQUEST' ) ) {
			return '';
		}

		ob_start();

		$content = [
			'image_url' => GIVE_PLUGIN_URL . 'assets/dist/images/give-icon-full-circle.svg',
			'image_alt' => __( 'GiveWP Icon', 'give' ),
			'heading'   => __( 'No donors found.', 'give' ),
			'help'      => sprintf(
				/* translators: 1: Opening anchor tag. 2: Closing anchor tag. */
				__( 'Need help? Learn more about %1$sDonors%2$s.', 'give' ),
				'<a href="http://docs.givewp.com/core-donors/">',
				'</a>'
			),
		];

		include_once GIVE_PLUGIN_DIR . 'includes/admin/views/blank-slate.php';

		return ob_get_clean();
	}
}

Give_Donor_Wall_Block::get_instance();

<?php
/**
 * Give Donation Grid Block Class
 *
 * @package     Give
 * @subpackage  Classes/Blocks
 * @copyright   Copyright (c) 2019, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.0.2
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Donation_Form_Grid_Block Class.
 *
 * This class handles donation forms block.
 *
 * @since 2.0.2
 */
class Give_Donation_Form_Grid_Block {
	/**
	 * Instance.
	 *
	 * @since
	 * @access private
	 * @var Give_Donation_Form_Grid_Block
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
	 * @return Give_Donation_Form_Grid_Block
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
	 * @since  2.0.2
	 * @access private
	 */
	private function init() {
		add_action( 'init', array( $this, 'register_block' ), 999 );
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
			'give/donation-form-grid',
			array(
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'formsPerPage'      => array(
						'type'    => 'string',
						'default' => '12',
					),
					'formIDs'           => array(
						'type'    => 'array',
						'default' => [],
					),
					'excludeForms'   => array(
						'type'    => 'boolean',
						'default' => false,
					),
                    'excludedFormIDs'   => array(
						'type'    => 'array',
						'default' => [],
					),
					'orderBy'           => array(
						'type'    => 'string',
						'default' => 'date',
					),
					'order'             => array(
						'type'    => 'string',
						'default' => 'DESC',
					),
					'categories'        => array(
						'type'    => 'array',
						'default' => [],
					),
					'tags'              => array(
						'type'    => 'array',
						'default' => [],
					),
					'columns'           => array(
						'type'    => 'string',
						'default' => '1',
					),
                    'imageSize'           => array(
						'type'    => 'string',
						'default' => 'medium',
					),
                    'imageHeight'           => array(
						'type'    => 'string',
						'default' => 'auto',
					),
					'showTitle'         => array(
						'type'    => 'boolean',
						'default' => true,
					),
					'showExcerpt'       => array(
						'type'    => 'boolean',
						'default' => true,
					),
                    'excerptLength'     => array(
                        'type'    => 'integer',
                        'default' => 16,
                    ),
					'showGoal'          => array(
						'type'    => 'boolean',
						'default' => true,
					),
                    'showProgressBar'          => array(
                        'type'    => 'boolean',
                        'default' => true,
                    ),
					'showFeaturedImage' => array(
						'type'    => 'boolean',
						'default' => true,
					),
                    'showDonateButton' => array(
						'type'    => 'boolean',
						'default' => true,
					),
                    'tagBackgroundColor' => array(
						'type'    => 'string',
						'default' => '#69b86b',
					),
                    'tagTextColor' => array(
                        'type'    => 'string',
                        'default' => '#ffffff',
                    ),
                    'donateButtonTextColor' => array(
                        'type'    => 'string',
                        'default' => '#69b86b',
                    ),
					'displayType'       => array(
						'type'    => 'string',
						'default' => 'redirect',
					),
                    'paged' => array(
                        'type'    => 'boolean',
                        'default' => true,
                    ),
                    'filterOptions'       => array(
                        'type'    => 'string',
                        'default' => 'tags',
                    ),
                    'imageHeightOptions'       => array(
                        'type'    => 'string',
                        'default' => 'auto',
                    ),
                    'progressBarColor'  => array(
                        'type' => 'string',
                        'default' => '#69b86b'
                    )
				),
			)
		);
	}

	/**
	 * Block render callback
	 *
	 * @param array $attributes Block parameters.
	 *
	 * @access public
	 * @return string;
	 */
	public function render_block( $attributes ) {
		$parameters = array(
			'forms_per_page'      => absint( $attributes['formsPerPage'] ),
			'ids'                 => implode(',', $this->getAsArray($attributes['formIDs'] ) ),
			'exclude'             => implode(',', $this->getAsArray($attributes['excludedFormIDs'] ) ),
			'orderby'             => $attributes['orderBy'],
			'order'               => $attributes['order'],
			'cats'                => implode(',', $this->getAsArray($attributes['categories'] ) ),
			'tags'                => implode(',', $this->getAsArray($attributes['tags'] ) ),
			'columns'             => $attributes['columns'],
			'show_title'          => $attributes['showTitle'],
			'show_goal'           => $attributes['showGoal'],
			'show_excerpt'        => $attributes['showExcerpt'],
            'excerpt_length'      => $attributes['excerptLength'],
			'show_featured_image' => $attributes['showFeaturedImage'],
			'show_donate_button'  => $attributes['showDonateButton'],
			'tag_background_color' => $attributes['tagBackgroundColor'],
            'tag_text_color' => $attributes['tagTextColor'],
            'donate_button_text_color' => $attributes['donateButtonTextColor'],
			'display_style'       => $attributes['displayType'],
            'paged'               => $attributes['paged'],
            'image_size'          => $attributes['imageSize'],
            'image_height'        => $attributes['imageHeight'],
            'image_height_options' => $attributes['imageHeightOptions'],
            'progress_bar_color'  => $attributes['progressBarColor']
        );

		$html = give_form_grid_shortcode( $parameters );
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
	 * Return formatted notice when shortcode return empty string
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

		$content = array(
			'image_url' => GIVE_PLUGIN_URL . 'assets/dist/images/give-icon-full-circle.svg',
			'image_alt' => __( 'Give Icon', 'give' ),
			'heading'   => __( 'No donation forms found.', 'give' ),
			'message'   => __( 'The first step towards accepting online donations is to create a form.', 'give' ),
			'cta_text'  => __( 'Create Donation Form', 'give' ),
			'cta_link'  => admin_url( 'post-new.php?post_type=give_forms' ),
			'help'      => sprintf(
				/* translators: 1: Opening anchor tag. 2: Closing anchor tag. */
				__( 'Need help? Get started with %1$sGive 101%2$s.', 'give' ),
				'<a href="http://docs.givewp.com/give101/" target="_blank">',
				'</a>'
			),
		);

		include_once GIVE_PLUGIN_DIR . 'includes/admin/views/blank-slate.php';

		return ob_get_clean();
	}
}

Give_Donation_Form_Grid_Block::get_instance();

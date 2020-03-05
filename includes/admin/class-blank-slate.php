<?php
/**
 * Give Blank Slate Class
 *
 * @package     Give
 * @subpackage  Admin
 * @copyright   Copyright (c) 2017, GiveWP
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8.13
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Give_Blank_Slate {
	/**
	 * The current screen ID.
	 *
	 * @since  1.8.13
	 * @var string
	 * @access public
	 */
	public $screen = '';

	/**
	 * Whether at least one donation form exists.
	 *
	 * @since  1.8.13
	 * @var bool
	 * @access private
	 */
	private $form = false;

	/**
	 * Whether at least one donation exists.
	 *
	 * @since  1.8.13
	 * @var bool
	 * @access private
	 */
	private $donation = false;

	/**
	 * Whether at least one donor exists.
	 *
	 * @since  1.8.13
	 * @var bool
	 * @access private
	 */
	private $donor = false;

	/**
	 * The content of the blank slate panel.
	 *
	 * @since  1.8.13
	 * @var array
	 * @access private
	 */
	private $content = array();

	/**
	 * Constructs the Give_Blank_Slate class.
	 *
	 * @since 1.8.13
	 */
	public function __construct() {
		$this->screen = get_current_screen()->id;
	}

	/**
	 * Initializes the class and hooks into WordPress.
	 *
	 * @since 1.8.13
	 */
	public function init() {
		// Bail early if screen cannot be detected.
		if ( empty( $this->screen ) ) {
			return null;
		}

		$content = array();

		// Define content and hook into the appropriate action.
		switch ( $this->screen ) {
			// Forms screen.
			case 'edit-give_forms':
				$this->form = $this->post_exists( 'give_forms' );

				if ( $this->form ) {
					// Form exists. Bail out.
					return false;
				} else {
					// No forms exist.
					$content = $this->get_content( 'no_forms' );
				}

				add_action( 'manage_posts_extra_tablenav', array( $this, 'render' ) );
				break;
			// Donations screen.
			case 'give_forms_page_give-payment-history':
				$this->form     = $this->post_exists( 'give_forms' );
				$this->donation = $this->post_exists( 'give_payment' );

				if ( $this->donation ) {
					// Donation exists. Bail out.
					return false;
				} elseif ( ! $this->form ) {
					// No forms and no donations exist.
					$content = $this->get_content( 'no_donations_or_forms' );
				} else {
					// No donations exist but a form does exist.
					$content = $this->get_content( 'no_donations' );
				}

				add_action( 'give_payments_page_bottom', array( $this, 'render' ) );
				break;
			// Donors screen.
			case 'give_forms_page_give-donors':
				$this->form  = $this->post_exists( 'give_forms' );
				$this->donor = $this->donor_exists();

				if ( $this->donor ) {
					// Donor exists. Bail out.
					return false;
				} elseif ( ! $this->form ) {
					// No forms and no donors exist.
					$content = $this->get_content( 'no_donors_or_forms' );
				} else {
					// No donors exist but a form does exist.
					$content = $this->get_content( 'no_donors' );
				}

				add_action( 'give_donors_table_bottom', array( $this, 'render' ) );
				break;
			default:
				return null;
		}

		$this->content = $content;

		// Hide non-essential UI elements.
		add_action( 'admin_head', array( $this, 'hide_ui' ) );
	}

	/**
	 * Renders the blank slate message.
	 *
	 * @since 1.8.13
	 *
	 * @param string $which The location of the list table hook: 'top' or 'bottom'.
	 */
	public function render( $which = 'bottom' ) {
		// Bail out to prevent content from rendering twice.
		if ( 'top' === $which ) {
			return null;
		}

		$screen = $this->screen;

		/**
		 * Filters the content of the blank slate.
		 *
		 * @since 1.8.13
		 *
		 * @param array $content {
		 *    Array of blank slate content.
		 *
		 *    @type string $image_url URL of the blank slate image.
		 *    @type string $image_alt Image alt text.
		 *    @type string $heading   Heading text.
		 *    @type string $message   Body copy.
		 *    @type string $cta_text  Call to action text.
		 *    @type string $cta_link  Call to action URL.
		 *    @type string $help      Help text.
		 * }
		 *
		 * @param string $screen The current screen ID.
		 */
		$content = apply_filters( 'give_blank_slate_content', $this->content, $screen );

		$template_path = GIVE_PLUGIN_DIR . 'includes/admin/views/blank-slate.php';

		include $template_path;
	}

	/**
	 * Hides non-essential UI elements when blank slate content is on screen.
	 *
	 * @since 1.8.13
	 */
	function hide_ui() {
		?>
		<style type="text/css">
			.give-filters,
			.search-box,
			.subsubsub,
			.wp-list-table,
			.tablenav.top,
			.give_forms_page_give-payment-history .tablenav.bottom,
			.give_forms_page_give-donors .tablenav.bottom,
			.tablenav-pages {
				display: none;
			}
		</style>
		<?php
	}

	/**
	 * Determines if at least one post of a given post type exists.
	 *
	 * @since 1.8.13
	 *
	 * @param string $post_type Post type used in the query.
	 * @return bool True if post exists, otherwise false.
	 */
	private function post_exists( $post_type ) {
		// Attempt to get a single post of the post type.
		$query = new WP_Query(
			array(
				'post_type'              => $post_type,
				'posts_per_page'         => 1,
				'no_found_rows'          => false,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
				'fields'                 => 'ids',
				'post_status'            => array( 'any', 'trash' ),
			)
		);

		return $query->have_posts();
	}

	/**
	 * Determines if at least one donor exists.
	 *
	 * @since 1.8.13
	 *
	 * @return bool True if donor exists, otherwise false.
	 */
	private function donor_exists() {
		$donors = Give()->donors->get_donors( array( 'number' => 1 ) );

		return ! empty( $donors );
	}

	/**
	 * Gets the content of a blank slate message based on provided context.
	 *
	 * @since 1.8.13
	 *
	 * @param string $context The key used to determine which content is returned.
	 * @return array Blank slate content.
	 */
	private function get_content( $context ) {
		// Define default content.
		$defaults = array(
			'image_url' => GIVE_PLUGIN_URL . 'assets/dist/images/give-icon-full-circle.svg',
			'image_alt' => __( 'GiveWP Icon', 'give' ),
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

		// Define contextual content.
		$content = array(
			'no_donations_or_forms' => array(
				'heading' => __( 'No donations found.', 'give' ),
				'message' => __( 'Your donation history will appear here, but first, you need a donation form!', 'give' ),
			),
			'no_donations'          => array(
				'heading'  => __( 'No donations found.', 'give' ),
				'message'  => __( 'When your first donation arrives, a record of the donation will appear here.', 'give' ),
				'cta_text' => __( 'View All Forms', 'give' ),
				'cta_link' => admin_url( 'edit.php?post_type=give_forms' ),
				'help'     => sprintf(
					/* translators: 1: Opening anchor tag. 2: Closing anchor tag. */
					__( 'Need help? Learn more about %1$sDonations%2$s.', 'give' ),
					'<a href="http://docs.givewp.com/core-donations/">',
					'</a>'
				),
			),
			'no_donors_or_forms'    => array(
				'heading' => __( 'No donors  found.', 'give' ),
				'message' => __( 'Your donor history will appear here, but first, you need a donation form!', 'give' ),
			),
			'no_donors'             => array(
				'heading'  => __( 'No donors found.', 'give' ),
				'message'  => __( 'When your first donation arrives, the donor will appear here.', 'give' ),
				'cta_text' => __( 'View All Forms', 'give' ),
				'cta_link' => admin_url( 'edit.php?post_type=give_forms' ),
				'help'     => sprintf(
					/* translators: 1: Opening anchor tag. 2: Closing anchor tag. */
					__( 'Need help? Learn more about %1$sDonors%2$s.', 'give' ),
					'<a href="http://docs.givewp.com/core-donors/">',
					'</a>'
				),
			),
		);

		if ( isset( $content[ $context ] ) ) {
			// Merge contextual content with defaults.
			return wp_parse_args( $content[ $context ], $defaults );
		} else {
			// Return defaults if context is undefined.
			return $defaults;
		}
	}
}

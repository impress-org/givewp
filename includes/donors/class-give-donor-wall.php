<?php
/**
 * Donor Wall
 *
 * @package     Give
 * @subpackage  Classes/Give_Donor_Wall
 * @copyright   Copyright (c) 2020, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
use Give\Donations\ValueObjects\DonationMetaKeys;
use Give\Framework\Database\DB;

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Give_Donor_Wall Class
 *
 * This class handles donors.
 *
 * @since 2.2.0
 */
class Give_Donor_Wall {

	/**
	 * Instance.
	 *
	 * @since  2.2.0
	 * @access private
	 * @var Give_Donor_Wall
	 */
	private static $instance;

	/**
	 * Singleton pattern.
	 *
	 * @since  2.2.0
	 * @access private
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  2.2.0
	 * @access public
	 * @return Give_Donor_Wall
	 */
	public static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();

			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since  2.2.0
	 *
	 * @return void
	 */
	public function setup_actions() {

		add_shortcode( 'give_donor_wall', [ $this, 'render_shortcode' ] );

		add_action( 'wp_ajax_give_get_donor_comments', [ $this, 'ajax_handler' ] );
		add_action( 'wp_ajax_nopriv_give_get_donor_comments', [ $this, 'ajax_handler' ] );

	}


    /**
     * Displays donors in a grid layout.
     *
     * @since 4.16.9 Added additional sanitization to donor output.
     * @since 4.13.2 add strip_shortcodes to the html output
     * @since 4.3.1 remove redundant _give_redirect_form_id() function.
     * @since 3.7.0 Sanitize attributes
     * @since 2.27.0 Moved AJAX nonce verification to ajax_handler method.
     * @since  2.2.0
     *
     * @param array $atts                {
     *                                   Optional. Attributes of the donor wall shortcode.
     *
     * @type int    $donors_per_page     Number of donors per page. Default '20'.
     * @type int    $form_id             The donation form to filter donors by. Default is all forms (no filter).
     * @type bool   $paged               Whether to paginate donors. Default 'true'.
     * @type string $ids                 A comma-separated list of donor IDs to display. Default empty.
     * @type string $columns             Maximum columns to display. Default 'best-fit'.
     *                                   Accepts 'best-fit', '1', '2', '3', '4'.
     * @type bool   $show_avatar         Whether to display the donor's gravatar image if available. Default 'true'.
     * @type bool   $show_name           Whether to display the donor's full name, first and last. Default 'true'.
     * @type bool   $show_company_name   Whether to display the donor's company name. Default 'false'.
     * @type bool   $show_total          Whether to display the donor's donation amount. Default 'true'.
     * @type bool   $show_comments       Whether to display the donor's comment if they left one. Default 'true'.
     * @type int    $comment_length      The number of words to display for the comments before a "Read more" field
     * @type int    $only_comments       Whether to display the donors only with comment. Default 'false'.
     * @type bool   $show_time Whether to display date of the last donation. Default 'true'.
     *
     * @type string $readmore_text       Link label for modal in which donor can read full comment.
     * @type string $loadmore_text       Button label which will load more donor comments.
     * @type int    $avatar_size         Avatar image size in pixels without the "px". Default "75"
     * @type string $orderby             The order in which you want the donations to appear.
     *                                   Currently we are using this attribute internally and, it will sort donations by created date.
     * @type string $order               The order in which you want the donors to appear. Accepts "ASC". "DESC".
     *
     * }
     * @return string|bool The markup of the form grid or false.
     */
	public function render_shortcode( $atts ) {
        $atts = give_clean($atts);

		$give_settings = give_get_settings();

		$atts      = $this->parse_atts( $atts );

		$donations = $this->get_donation_data( $atts );
		$html      = '';

		if ( $donations ) {

			ob_start();

			foreach ( $donations as $donation ) {
                $donor = new Give_Donor($donation['_give_payment_donor_id']);
                // Give/templates/shortcode-donor-wall.php.
                give_get_template(
                    'shortcode-donor-wall',
                    [
                        $donation,
                        $give_settings,
                        $atts,
                        $donor
                    ]
                );
            }

			$html = ob_get_clean();

            // Strip shortcodes to prevent execution of user-supplied shortcode syntax.
            $html = give_strip_shortcodes_deep($html);

            // Return only donor html.
            if (
				isset( $atts['only_donor_html'] )
				&& wp_doing_ajax()
				&& $atts['only_donor_html']
			) {
				return $html;
			}
		}

		$temp_atts          = $atts;
		$temp_atts['paged'] = $atts['paged'] + 1;

		$more_btn_html = sprintf(
			'<input type="hidden" class="give-donor-wall-shortcode-attrs" data-shortcode="%s" data-nonce="%s">',
			rawurlencode( http_build_query( $atts ) ),
            wp_create_nonce( 'givewp-donor-wall-more' )
		);

		if ( $this->has_donations( $temp_atts ) ) {
			$more_btn_html .= sprintf(
				'<button class="give-donor__load_more give-button-with-loader"><span class="give-loading-animation"></span>%1$s</button>',
				$atts['loadmore_text']
			);
		}

		$html = $html
			? sprintf(
				'<div class="give-wrap give-grid-ie-utility"><div class="give-grid give-grid--%1$s">%2$s</div>%3$s</div>',
				esc_attr( $atts['columns'] ),
				$html,
				$more_btn_html
			)
			: '';

		return $html;
	}

	/**
	 * Parse shortcode attributes
	 *
	 * @since 2.30.0
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $atts Shortcode attributes.
	 *
	 * @return array
	 */
	public function parse_atts( $atts ) {
		$atts = shortcode_atts(
			[
				'donors_per_page'   => 12,
				'form_id'           => 0,
				'paged'             => 1,
				'ids'               => '',
                'cats'              => '',
                'tags'              => '',
				'columns'           => '3',
				'anonymous'         => true,
				'show_avatar'       => true,
				'show_name'         => true,
				'show_company_name' => false,
				'show_form'         => false,
				'show_total'        => true,
				'show_comments'     => true,
                'show_tributes'     => true,
                'comment_length'    => 140,
				'only_comments'     => false,
				'readmore_text'     => esc_html__( 'Read more', 'give' ),
				'loadmore_text'     => esc_html__( 'Load more', 'give' ),
				'avatar_size'       => 75,
                'color'             => "#219653",
				'orderby'           => 'post_date',
				'order'             => 'DESC',
				'hide_empty'        => true,  // Deprecated in 2.3.0
				'only_donor_html'   => false, // Only for internal use.,
                'show_time'         => true,
			],
			$atts,
			'give_donor_wall'
		);

		// Validate boolean attributes.
		$boolean_attributes = [
			'anonymous',
			'show_avatar',
			'show_name',
			'show_company_name',
			'show_total',
			'show_comments',
			'show_tributes',
			'hide_empty',
			'only_comments',
			'only_donor_html',
            'show_time'
		];

		foreach ( $boolean_attributes as $att ) {
			// Convert numeric to boolean.
			// It will prevent condition check against boolean value.
			if ( is_numeric( $atts[ $att ] ) ) {
				$atts[ $att ] = (bool) $atts[ $att ];
			}

			$atts[ $att ] = filter_var( $atts[ $att ], FILTER_VALIDATE_BOOLEAN );
		}

		// Validate numeric attributes.
		$numeric_attributes = [
			'donors_per_page',
			'paged',
			'comment_length',
			'avatar_size',
		];

		foreach ( $numeric_attributes as $att ) {
			// It will prevent condition check against numeric value.
			$atts[ $att ] = absint( $atts[ $att ] );
		}

		// Validate comma separated numeric attributes and keep original data format ( comma separated string).
		if ( ! empty( $atts['ids'] ) ) {
			$atts['ids'] = implode( ',', $this->split_string($atts['ids'], 'absint') );
		}

        // Validate Form IDs
        if ( ! empty( $atts['form_id'] ) ) {
            $atts['form_id'] = implode( ',', $this->split_string($atts['form_id'], 'absint') );
        }

        // Donation form categories
        if ( ! empty( $atts['cats'] ) ) {
            $atts['cats'] = $this->split_string($atts['cats']);
        }

        // Donation form tags
        if ( ! empty( $atts['tags'] ) ) {
            $atts['tags'] = $this->split_string($atts['tags']);
        }

		return $atts;
	}

	/**
	 * Get donors
	 *
	 * @since  2.2.0
	 * @access public
	 *
	 * @param array $donor_query Donor query.
	 *
	 * @return array
	 */
	public function get_donors( $donor_query ) {
		$donor_query = new Give_Donors_Query( $donor_query );

		return $donor_query->get_donors();
	}


	/**
	 * This function should return donor comment for ajax request.
	 *
     * @since 2.27.0 Check nonce for AJAX request to prevent scrapping, see https://github.com/impress-org/givewp/issues/6374.
	 * @since  2.2.0
	 * @access public
	 */
	public function ajax_handler() {
		$shortcode_atts = array_map( 'give_clean', wp_parse_args( rawurldecode( $_POST['data'] ) ) ); // @codingStandardsIgnoreLine

		// Get next page donor comments.
		$shortcode_atts['paged']           = $shortcode_atts['paged'] + 1;
		$shortcode_atts['only_donor_html'] = true;

        check_ajax_referer( 'givewp-donor-wall-more', 'nonce' );

		$donors_comment_html = $this->render_shortcode( $shortcode_atts );

		// Check if donor comment remaining.
		$temp_atts          = $shortcode_atts;
		$temp_atts['paged'] = $shortcode_atts['paged'] + 1;
		$has_donors         = $this->has_donations( $temp_atts ) ? 1 : 0;

		// Remove internal shortcode param.
		unset( $shortcode_atts['only_donor_html'] );

		wp_send_json(
			[
				'shortcode' => rawurlencode( http_build_query( $shortcode_atts ) ),
				'html'      => $donors_comment_html,
				'remaining' => $has_donors,
			]
		);
	}

	/**
	 * Get query params
	 *
     * @since 2.24.1
	 * @since 2.3.0
	 *
	 * @param  array $atts
	 *
	 * @return array
	 */
	private function get_query_param( $atts = [] ) {
		$valid_order   = [ 'ASC', 'DESC' ];
		$valid_orderby = [ 'post_date', 'donation_amount' ];

		$query_atts = [];

		$query_atts['order']         = in_array( $atts['order'], $valid_order ) ? $atts['order'] : 'DESC';
		$query_atts['orderby']       = in_array( $atts['orderby'], $valid_orderby ) ? $atts['orderby'] : 'post_date';
		$query_atts['limit']         = absint( $atts['donors_per_page'] );
		$query_atts['offset']        = absint( $atts['donors_per_page'] * ( $atts['paged'] - 1 ) );
        $query_atts['form_id']       = implode( '\',\'', array_map( 'absint', explode( ',', $atts['form_id'] ) ) );
        $query_atts['ids']           = implode( '\',\'', array_map( 'absint', explode( ',', $atts['ids'] ) ) );
		$query_atts['cats']          = $atts['cats'];
		$query_atts['tags']          = $atts['tags'];
		$query_atts['only_comments'] = ( true === $atts['only_comments'] );
		$query_atts['anonymous']     = ( true === $atts['anonymous'] );



		return $query_atts;
	}

    /**
     * Get donation data.
     *
     * @since TBD     Read the rendered values off the Donation model instead of unserializing raw meta rows.
     * @since 4.16.7.2       Restrict unserialize to prevent object instantiation.
     * @since 2.27.0  Change to read comment from donations meta table
     * @since 2.3.0
     *
     * @param  array  $atts
     *
     * @return array
     */
	private function get_donation_data( $atts = [] ) {
		// Bailout if donation does not exist.
		if ( ! ( $donation_ids = $this->get_donations( $atts ) ) ) {
			return [];
		}

		$donation_ids = array_map( 'absint', $donation_ids );

		$donations = give()->donations->prepareQuery()
			->whereIn( 'ID', $donation_ids )
			->getAll();

		if ( ! $donations ) {
			return [];
		}

		$donations_by_id = [];

		foreach ( $donations as $donation ) {
			$donations_by_id[ $donation->id ] = $donation;
		}

		$meta_by_donation_id = $this->get_donations_meta( $donation_ids );

		$results = [];

		/*
		 * Iterating the IDs rather than the models because get_donations() already sorted them by
		 * the shortcode's order attributes and the model query does not preserve that order.
		 */
		foreach ( $donation_ids as $donation_id ) {
			if ( ! isset( $donations_by_id[ $donation_id ] ) ) {
				continue;
			}

			$donation = $donations_by_id[ $donation_id ];

			$results[ $donation_id ] = array_merge(
				isset( $meta_by_donation_id[ $donation_id ] ) ? $meta_by_donation_id[ $donation_id ] : [],
				[
					DonationMetaKeys::DONOR_ID   => $donation->donorId,
					DonationMetaKeys::FIRST_NAME => $donation->firstName,
					DonationMetaKeys::LAST_NAME  => $donation->lastName,
					DonationMetaKeys::EMAIL      => $donation->email,
					DonationMetaKeys::ANONYMOUS  => $donation->anonymous,
					DonationMetaKeys::FORM_ID    => $donation->formId,
					DonationMetaKeys::FORM_TITLE => $donation->formTitle,
					'donation_id'                => $donation->id,
					'donation_date'              => $donation->createdAt->format( 'Y-m-d H:i:s' ),
					'donor_comment'              => $donation->comment,
					'name_initial'               => give_get_name_initial(
						[
							'firstname' => (string) $donation->firstName,
							'lastname'  => (string) $donation->lastName,
						]
					),
				]
			);

			/* The template hides the company heading by testing isset(), so an empty company has to leave the key absent. */
			if ( $donation->company ) {
				$results[ $donation_id ][ DonationMetaKeys::COMPANY ] = $donation->company;
			}
		}

		return $results;
	}

	/**
	 * Get the donation meta the donor wall template — and the add-ons extending it — read by key.
	 *
	 * Values come back exactly as they are stored. The wall renders them as text, and decoding a
	 * stored payload is what let an object reach the template in the first place.
	 *
	 * @since TBD
	 *
	 * @param  int[] $donation_ids
	 *
	 * @return array Donation meta keyed by donation ID, then by meta key.
	 */
	private function get_donations_meta( $donation_ids ) {
		$meta = [];

		$rows = DB::table( 'give_donationmeta' )
			->select( 'donation_id', 'meta_key', 'meta_value' )
			->whereIn( 'donation_id', $donation_ids )
			->getAll();

		foreach ( (array) $rows as $row ) {
			$meta[ (int) $row->donation_id ][ $row->meta_key ] = $row->meta_value;
		}

		return $meta;
	}

	/**
	 * Get donation list for specific query
	 *
     * @since 3.17.2 fix - filter by only_comments attr
	 * @since 2.3.0
	 *
	 * @param  array $atts
	 *
	 * @return array
	 */
	private function get_donations( $atts = [] ) {
		global $wpdb;

		// Backward compatibility
		$donation_id_col = Give()->payment_meta->get_meta_type() . '_id';

		$query_params = $this->get_query_param( $atts );

		$sql   = "SELECT p1.ID FROM {$wpdb->posts} as p1";
		$where = " WHERE p1.post_status IN ('publish') AND p1.post_type = 'give_payment'";

		// exclude donation with zero amount from result.
		$sql   .= " INNER JOIN {$wpdb->donationmeta} as m1 ON (p1.ID = m1.{$donation_id_col})";
		$where .= " AND m1.meta_key='_give_payment_total' AND m1.meta_value>0";

		if ( $query_params['form_id'] ) {
			$sql   .= " INNER JOIN {$wpdb->donationmeta} as m2 ON (p1.ID = m2.{$donation_id_col})";
			$where .= " AND m2.meta_key='_give_payment_form_id' AND m2.meta_value IN ('{$query_params['form_id']}')";
		}

		// Get donations only from specific donors.
		if ( $query_params['ids'] ) {
			$sql   .= " INNER JOIN {$wpdb->donationmeta} as m3 ON (p1.ID = m3.{$donation_id_col})";
			$where .= " AND m3.meta_key='_give_payment_donor_id' AND m3.meta_value IN ('{$query_params['ids']}')";
		}

		// exclude donations which does not has donor comment.
		if ( $query_params['only_comments'] ) {
			$sql   .= " INNER JOIN {$wpdb->donationmeta} as m4 ON (p1.ID = m4.{$donation_id_col})";
            $where .= " AND m4.meta_key='_give_donation_comment'";
		}

		// exclude anonymous donation form query based on query parameters.
		if (
			! $query_params['anonymous']
			|| $query_params['only_comments']
		) {
			$where .= " AND p1.ID NOT IN ( SELECT DISTINCT({$donation_id_col}) FROM {$wpdb->donationmeta} WHERE meta_key='_give_anonymous_donation' AND meta_value='1')";
		}

        // Handle Taxonomy
        $args = [
            'post_type' => 'give_forms',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'tax_query' => [],
        ];

        // Categories
        if ( is_array($atts['cats'])) {
            $args['tax_query']['conditions'] = ['relation' => 'OR'];

            foreach ($atts['cats'] as $category) {
                $args['tax_query']['conditions'][] = [
                    'operator' => 'IN',
                    'taxonomy' => 'give_forms_category',
                    'field' => 'slug',
                    'terms' => $category,
                ];
            }
        }

        // Tags
        if ( is_array($atts['tags'])) {
            if (empty($args['tax_query'])) {
                $args['tax_query']['conditions'] = ['relation' => 'OR'];
            }

            foreach($atts['tags'] as $tag) {
                $args['tax_query']['conditions'][] = [
                    'operator' => 'IN',
                    'taxonomy' => 'give_forms_tag',
                    'field' => 'slug',
                    'terms' => $tag,
                ];
            }
        }

        if ( ! empty( $args['tax_query'] ) ) {
            $query = new WP_Query( $args );

            if ( ! empty($query->posts) ) {
                $form_ids = implode("','", $query->posts );
                $sql   .= " INNER JOIN {$wpdb->donationmeta} as m4 ON (p1.ID = m4.{$donation_id_col})";
                $where .= " AND m4.meta_key='_give_payment_form_id' AND m4.meta_value IN ('{$form_ids}')";
            }
        }

		// order by query based on parameter.
		if ( 'donation_amount' === $query_params['orderby'] ) {
			$order = " ORDER BY m1.meta_value+0 {$query_params['order']}";
		} else {
			$order = " ORDER BY p1.{$query_params['orderby']} {$query_params['order']}, p1.ID {$query_params['order']}";
		}

		$limit  = " LIMIT {$query_params['limit']}";
		$offset = " OFFSET {$query_params['offset']}";

		$sql .= $where . $order . $limit . $offset;

		return $wpdb->get_col( $sql );
	}

	/**
	 * Get donor comments
	 *
	 * @since 2.3.0
	 *
	 * @param array $donations_data
	 *
	 * @return array
	 */
	private function get_donor_comments( $donations_data = [] ) {
		global $wpdb;
		$comments = [];

		// Bailout.
		if ( empty( $donations_data ) ) {
			return $comments;
		}

		// Backward compatibility.
		if (
			! give_has_upgrade_completed( 'v230_move_donor_note' )
			|| ! give_has_upgrade_completed( 'v230_move_donation_note' )
		) {
			foreach ( $donations_data as $id => $data ) {
				$comment         = give_get_donor_donation_comment( $id, $data['_give_payment_donor_id'] );
				$comments[ $id ] = ! empty( $comment ) ? $comment->comment_content : '';
			}

			return $comments;
		}

		$sql   = "SELECT c1.comment_parent as donation_id, c1.comment_content as comment FROM {$wpdb->give_comments} as c1";
		$sql  .= " INNER JOIN {$wpdb->give_commentmeta} as cm1 ON (c1.comment_ID=cm1.give_comment_id)";
		$where = [];

		foreach ( $donations_data as $id => $data ) {
			// Do not fetch comment for anonymous donation.
			if ( ! empty( $data['_give_anonymous_donation'] ) ) {
				continue;
			}

			$where[] = "(c1.comment_parent={$id} AND cm1.meta_key='_give_donor_id' AND cm1.meta_value={$data['_give_payment_donor_id']})";
		}

		$where  = ' WHERE ' . implode( ' OR ', $where );
		$where .= " AND c1.comment_type='donor_donation'";

		$sql = $sql . $where;

		$comments = (array) $wpdb->get_results( $sql );

		if ( ! empty( $comments ) ) {
			$comments = array_combine(
				wp_list_pluck( $comments, 'donation_id' ),
				wp_list_pluck( $comments, 'comment' )
			);
		}

		return $comments;
	}

	/**
	 * Check if donation exist or not for specific query
	 *
	 * @since 2.3.0
	 *
	 * @param  array $atts
	 *
	 * @return bool
	 */
	private function has_donations( $atts = [] ) {
		return (bool) $this->get_donations( $atts );
	}

    /**
     * @since 2.20.0
     *
     * @param string $string
     * @param null|callable $filter
     * @param string $separator
     *
     * @return array
     */
    private function split_string($string, $filter = null, $separator = ',') {
        if ( false === strpos( $string, $separator ) ) {
            $string = trim( $string );

            if (is_callable($filter)) {
                $string = $filter($string);
            }

            return [$string];
        }

        return array_filter(
            array_map(
                static function( $value ) use ($filter) {
                    $value = trim( $value );

                    if (is_callable($filter)) {
                        return $filter($value);
                    }
                    return $value;
                },
                explode( $separator, $string )
            )
        );
    }
}

// Initialize shortcode.
Give_Donor_Wall::get_instance();

<?php
/**
 * Forms Query
 *
 * @package     Give
 * @subpackage  Classes/Form
 * @copyright   Copyright (c) 2019, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Give_Forms_Query Class
 * Note: only for internal use. This class is under development, so use it at your risk.
 *
 * This class is for retrieving forms data.
 *
 * @since 2.5.0
 */
class Give_Forms_Query {
	/**
	 * Preserve args
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var    array
	 */
	public $_args = array();

	/**
	 * The args to pass to the give_get_forms() query
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var    array
	 */
	public $args = array();

	/**
	 * The forms found based on the criteria set
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @var    array
	 */
	public $forms = array();

	/**
	 * Default query arguments.
	 *
	 * Not all of these are valid arguments that can be passed to WP_Query. The ones that are not, are modified before
	 * the query is run to convert them to the proper syntax.
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @param  $args array The array of arguments that can be passed in and used for setting up this form query.
	 */
	public function __construct( $args = array() ) {
		$defaults = array(
			'output'    => 'forms',
			'post_type' => array( 'give_forms' ),
		);

		// We do not want WordPress to handle meta cache because WordPress stores in under `post_meta` key and cache object while we want it under `form_meta`.
		// Similar for term cache
		$args['update_post_meta_cache'] = false;

		$this->args = $this->_args = wp_parse_args( $args, $defaults );
	}

	/**
	 * Retrieve forms.
	 *
	 * The query can be modified in two ways; either the action before the
	 * query is run, or the filter on the arguments (existing mainly for backwards
	 * compatibility).
	 *
	 * @since  2.5.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_forms() {
		global $post;

		$results     = array();
		$this->forms = array();
		$cache_key   = Give_Cache::get_key( 'give_form_query', $this->args, false );
		$this->forms = Give_Cache::get_db_query( $cache_key );

		// Return cached result.
		if ( ! is_null( $this->forms ) ) {
			return $this->forms;
		}

		/* @var WP_Query $query */
		$query = new WP_Query( $this->args );

		$custom_output = array(
			'forms',
			'give_forms',
		);

		if ( $query->have_posts() ) {
			$this->update_meta_cache( wp_list_pluck( $query->posts, 'ID' ) );

			if ( ! in_array( $this->args['output'], $custom_output ) ) {
				$results = $query->posts;

			} else {
				$previous_post = $post;

				while ( $query->have_posts() ) {
					$query->the_post();

					$form_id = get_post()->ID;
					$form    = new Give_Donate_Form( $form_id );

					$this->forms[] = apply_filters( 'give_form', $form, $form_id, $this );
				}

				wp_reset_postdata();

				// Prevent nest loop from producing unexpected results.
				if ( $previous_post instanceof WP_Post ) {
					$post = $previous_post;
					setup_postdata( $post );
				}

				$results = $this->forms;
			}
		}

		Give_Cache::set_db_query( $cache_key, $results );

		return $results;
	}

	/**
	 * Update forms meta cache
	 *
	 * @since  2.5.0
	 * @access private
	 *
	 * @param $form_ids
	 */
	public static function update_meta_cache( $form_ids ) {
		// Exit.
		if ( empty( $form_ids ) ) {
			return;
		}

		update_meta_cache( Give()->form_meta->get_meta_type(), $form_ids );
	}
}

<?php
/**
 * Give - Stripe Core | Deprecated Filter Hooks
 *
 * @since 2.5.0
 *
 * @package    Give
 * @subpackage Stripe Core
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Bailout, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$give_stripe_map_deprecated_filters = give_stripe_deprecated_filters();

foreach ( $give_stripe_map_deprecated_filters as $new => $old ) {
	add_filter( $new, 'give_stripe_deprecated_filter_mapping', 10, 4 );
}

/**
 * Deprecated filters.
 *
 * @return array An array of deprecated Give filters.
 */
function give_stripe_deprecated_filters() {

	$deprecated_filters = array(
		// New filter hook                    Old filter hook.
		'give_stripe_get_connect_settings' => 'get_give_stripe_connect_options',
	);

	return $deprecated_filters;
}

/**
 * Deprecated filter mapping.
 *
 * @param mixed  $data
 * @param string $arg_1 Passed filter argument 1.
 * @param string $arg_2 Passed filter argument 2.
 * @param string $arg_3 Passed filter argument 3.
 *
 * @return mixed
 */
function give_stripe_deprecated_filter_mapping( $data, $arg_1 = '', $arg_2 = '', $arg_3 = '' ) {
	$give_stripe_map_deprecated_filters = give_stripe_deprecated_filters();
	$filter                             = current_filter();

	if ( isset( $give_stripe_map_deprecated_filters[ $filter ] ) ) {
		if ( has_filter( $give_stripe_map_deprecated_filters[ $filter ] ) ) {
			$data = apply_filters( $give_stripe_map_deprecated_filters[ $filter ], $data, $arg_1, $arg_2, $arg_3 );

			if ( ! defined( 'DOING_AJAX' ) ) {
				_give_deprecated_function(
					sprintf( /* translators: %s: filter name */
						__( 'The %s filter', 'give' ),
						$give_stripe_map_deprecated_filters[ $filter ]
					),
					'2.5.0',
					$filter
				);
			}
		}
	}

	return $data;
}

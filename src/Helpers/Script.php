<?php

namespace Give\Helpers\Script;

/**
 * Get script tag
 *
 * @param string $url
 * @param array  $args
 *
 * @return string
 * @since 2.7.0
 */
function getScripTag( $url, $args = [] ) {
	return sprintf(
		'<script src="%1$s" type="text/javascript"></script>',
		add_query_arg( array( 'ver' => GIVE_VERSION ), $url )
	);
}

/**
 * Get style tag
 *
 * @param string $url
 * @param array  $args
 *
 * @return string
 * @since 2.7.0
 */
function getStyleTag( $url, $args = [] ) {
	$args = wp_parse_args(
		$args,
		[ 'media' => 'all' ]
	);

	return sprintf(
		'<link rel="stylesheet" href="%1$s" media="%2$s"/>',
		add_query_arg( array( 'ver' => GIVE_VERSION ), $url ),
		$args['media']
	);
}

/**
 * Get localize script
 *
 * @param string $name
 * @param array  $data
 *
 * @return string
 * @since 2.7.0
 */
function getLocalizedScript( $name, $data ) {
	return sprintf(
		'<script> var %1$s = %2$s </script>',
		$name,
		wp_json_encode( $data )
	);
}

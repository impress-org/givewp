<?php
namespace Give\Helpers;

/**
 * Extract query param from URL
 *
 * @since 2.7.0
 *
 * @param string $url
 * @param string $queryParamName
 * @param mixed  $default
 *
 * @return string
 */
function getQueryParamFromURL( $url, $queryParamName, $default = '' ) {
	$queryArgs = wp_parse_args( parse_url( $url, PHP_URL_QUERY ) );

	return isset( $queryArgs[ $queryParamName ] ) ? give_clean( $queryArgs[ $queryParamName ] ) : $default;
}

/**
 * This function will change request url with other url.
 *
 * @since 2.7.0
 *
 * @param string $location Requested URL.
 * @param string $url URL.
 *
 * @return string
 */
function switchRequestedURL( $location, $url ) {
	$tmp    = explode( '?', $location, 2 );
	$tmp[0] = $url;

	$location = implode( '?', $tmp );

	return $location;
}

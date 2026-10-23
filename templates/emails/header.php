<?php
/**
 * Email Header
 *
 * @package     Give/Templates/Emails
 * @version     1.0
 * @since TBD Escape output.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
// This is the footer used if no others are available
?>
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
	</head>
	<body>

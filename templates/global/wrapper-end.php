<?php
/**
 * Content wrappers
 *
 * @package     Give
 * @subpackage  Templates/Global
 * @copyright   Copyright (c) 2016, GiveWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$template = get_option( 'template' );

switch ( $template ) {
	case 'twentyeleven' :
		echo '</div></div>';
		break;
	case 'twentytwelve' :
		echo '</div></div>';
		break;
	case 'twentythirteen' :
		echo '</div></div>';
		break;
	case 'twentyfourteen' :
		echo '</div></div></div>';
		get_sidebar( 'content' );
		break;
	case 'twentyfifteen' :
		echo '</div></div>';
		break;
	case 'twentynineteen' :
		echo '</div></div></div>';
		break;
	case 'x' :
		echo '</div></div></div></div>';
		break;
	case 'salient' :
		echo '</div></div></div>';
		break;
	case 'jupiter' :
		echo '</div></div></div>';
		break;
	case 'philanthropy-parent' :
		echo '</div></div></div></div>';
		break;
	case 'zerif-lite' :
		echo '</main></article></div></div></div>';
		break;
	case 'customizr' :
		echo '</div>';
		break;
	case 'catch-evolution' :
		echo '</div>';
		break;
	case 'twentyseventeen' :
		echo '</div>';
		break;
	default :
		echo apply_filters( 'give_default_wrapper_end', '</div></div>' );
		break;
}

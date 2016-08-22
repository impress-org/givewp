/*!
 * Give Admin Forms JS
 *
 * @description: The Give Admin Settings scripts. Only enqueued on the give-settings page; used for tabs and other show/hide functionality
 * @package:     Give
 * @since:       1.5
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
jQuery(document).ready(function ($) {

	/**
	 *  Sortable payment gateways
	 */
	var $payment_gateways = jQuery( '.cmb-type-enabled-gateways ul', '#cmb2-metabox-payment_gateways' );
	if( $payment_gateways.length ){
		$payment_gateways.sortable();
	}

});

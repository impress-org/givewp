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

    // when page loads, make tab active
    tab_switch();

    // show and hide tab content on tab clicks
    $('.nav-tab').on('click', function (e) {
        e.preventDefault();
        tab_switch($(this));
    });

    /**
     * TAB SWITCH
     *
     * Changes the tab content when tab is clicked.
     *
     * @param $tab
     *
     * @since 1.0.0
     */
    function tab_switch($tab) {

        var id, tab;

        // if $tab was not set, get the tab from the query string
        // default to 'general' if no query string present
        if (typeof($tab) === 'undefined') {
            id = get_query_string('tab').length ? get_query_string('tab') : 'general';
        } else {
            // get id and content location from the chosen tab
            id = $tab.attr('id');
            id = id.replace('tab-', '');
        }

        $tab = $('.give_settings_page').find("[data-tab='" + id + "']");

        // hide all tabs and show current tab
        $('.cmb-form').hide();
        $tab.show();

        // remove and set the Wordpress active tab class on the tab navigation
        $('.nav-tab').removeClass('nav-tab-active');
        $('#tab-' + id).addClass('nav-tab-active').blur();

        // change the browser URL to reflect the current tab; 
        // this allows WordPress to change to this tab on save
        if (history.pushState) {

            var stateObject = {dummy: true},
                postvar = 'post_type=give_forms&page=give-settings';

            var url = window.location.protocol
                + "//"
                + window.location.host
                + window.location.pathname
                + '?'
                + postvar
                + '&tab=' + id;

            history.pushState(stateObject, $(document).find('title').text(), url);
        }
    }

    /**
     * Gets the query string.
     *
     * @param name
     * @returns {string}
     *
     * @since 1.5
     */
    function get_query_string(name) {
        var regex, results;
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        regex = new RegExp("[\\?&]" + name + "=([^&#]*)");
        results = regex.exec(location.search);
        return results === null ?
            "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

	/**
	 *  Sortable payment gateways
	 */
	var $payment_gateways = jQuery( '.cmb-type-enabled-gateways ul', '#cmb2-metabox-payment_gateways' );
	if( $payment_gateways.length ){
		$payment_gateways.sortable();
	}

});

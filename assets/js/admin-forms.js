/*!
 * Give Admin Forms JS
 *
 * @description: The Give Admin Forms scripts. Only enqueued on the give_forms CPT; used to validate fields, show/hide, and other functions
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2014, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ( $ ) {

	/**
	 * Default Radio Button
	 *
	 * @description: Allow only one radio button to be checked at a time
	 * @since: 1.0
	 */
	var handle_default_radio = function () {
		"use strict";
		var default_radio = $( 'input.donation-level-radio' );
		var repeatable_rows = $( '#_give_donation_levels_repeat > .cmb-repeatable-grouping' );
		var number_of_prices = repeatable_rows.length;

		default_radio_one_checked_radio( default_radio );

		////Ensure that there's always a default price level checked
		$( 'body' ).on( 'cmb2_add_row', function ( e ) {
			var default_radio = $( 'input.donation-level-radio' );
			default_radio_one_checked_radio( default_radio );
		} );

		////When a row is removed containing the default selection then revert default to first repeatable row
		$( 'body' ).on( 'cmb2_remove_row', function ( e ) {
			var default_radio = $( 'input.donation-level-radio' );
			var repeatable_rows = $( '#_give_donation_levels_repeat > .cmb-repeatable-grouping' );
			if ( default_radio.is( ':checked' ) === false ) {
				repeatable_rows.first().find( 'input.donation-level-radio' ).prop( 'checked', true );
			}
		} );

		////If only one price then that one is default
		if ( number_of_prices === 1 ) {
			default_radio.prop( 'checked', true );
		}

	};

	/**
	 * Only one radio checked
	 *
	 * @description: This function runs when a new row is added and also on DOM load
	 * @since: 1.0
	 */
	var default_radio_one_checked_radio = function ( default_radio ) {
		default_radio.on( 'change', function () {
			default_radio.not( this ).prop( 'checked', false );
		} );
	};


	/**
	 * Toggle Conditional Form Fields
	 *
	 *  @since: 1.0
	 */
	var toggle_conditional_form_fields = function () {

		//Price Option
		var price_option = $( '.cmb2-id--give-price-option input:radio' );

		price_option.on( 'change', function () {

			var price_option_val = $( '.cmb2-id--give-price-option input:radio:checked' ).val();
			if ( price_option_val === 'set' ) {
				$( '.cmb2-id--give-set-price' ).show();
				$( '.cmb2-id--give-levels-header, .cmb2-id--give-levels-header + .cmb-repeat-group-wrap, .cmb2-id--give-display-style' ).hide();
			} else {
				$( '.cmb2-id--give-set-price' ).hide();
				$( '.cmb2-id--give-levels-header, .cmb2-id--give-levels-header + .cmb-repeat-group-wrap, .cmb2-id--give-display-style' ).show();
			}
		} ).change();


	};

	//Handle Repeatable Row ID
	var handle_repeatable_row_ID = function () {

		//Ensure for new posts that the repeater is filled
		if ( $( '.give-level-id' ).text() === '' ) {
			var row_group = $( '.cmb-repeatable-grouping' );
			//loop through all repeatable rows and set vals
			row_group.each( function ( index, object ) {

				var row_id = $( object ).data( 'iterator' ) + 1;

				$( object ).find( '.give-level-id' ).text( row_id );
				$( object ).find( '.give-level-id-input' ).val( row_id );

			} );
		}

		$( 'body' ).on( 'cmb2_add_row', function ( event, row ) {
			console.log( row );
			set_row_ids( row );

		} );
		$( 'body' ).on( 'cmb2_shift_rows_complete', function ( event, self ) {

			var row_group = $( '.cmb-repeatable-grouping' );
			//loop through all repeatable rows and set vals
			row_group.each( function ( index, object ) {

				var row_id = $( object ).find( 'input.give-level-id-input' ).val();

				$( object ).find( '.give-level-id' ).text( row_id );

			} );

		} );


		function set_row_ids( row ) {
			//Get the row ID and add 1 (iterator starts at 0 in CMB2)
			var row_id = $( row ).data( 'iterator' ) + 1;
			//Add row ID value to hidden field
			$( row ).find( 'input.give-hidden' ).val( row_id );
			//Add row ID to displayed ID
			$( row ).find( '.give-level-id' ).text( row_id );
		}


	};

	/**
	 * Misc Cleanup
	 *
	 * @description: Clean up and tweaks
	 * @since: 1.0
	 */
	function misc_cleanup() {

		//No Value = Placeholders: determine if value is 0.00 and remove if so in favor of placeholders
		$( '.cmb2-text-money' ).each( function ( index, object ) {
			var this_val = parseInt( $( object ).val() );
			if ( !this_val ) {
				$( object ).removeAttr( 'value' );
			}
		} );

	}


	//On DOM Ready
	$( function () {

		handle_default_radio();
		toggle_conditional_form_fields();
		handle_repeatable_row_ID();
		misc_cleanup();

	} );


})( jQuery );
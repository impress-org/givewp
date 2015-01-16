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
	 * @description: Allow only one to be checked
	 * @since: 1.0
	 */
	var handle_default_radio = function () {
		"use strict";
		var default_radio = $( 'input.donation-level-radio' );
		var repeatable_rows = $( '#_give_donation_levels_repeat > .cmb-repeatable-grouping' );
		var number_of_prices = repeatable_rows.length;

		default_radio_one_checked_radio( default_radio );

		//Ensure that there's always a default price level checked
		$( 'body' ).on( 'click', '#_give_donation_levels_repeat button.cmb-add-group-row', function ( e ) {
			var default_radio = $( 'input.donation-level-radio' );
			default_radio_one_checked_radio( default_radio );
		} );

		//When a row is removed containing the default selection then revert default to first repeatable row
		$( 'body' ).on( 'cmb2_remove_row', function ( e ) {
			var default_radio = $( 'input.donation-level-radio' );
			var repeatable_rows = $( '#_give_donation_levels_repeat > .cmb-repeatable-grouping' );
			if ( default_radio.is( ':checked' ) === false ) {
				repeatable_rows.first().find( 'input.donation-level-radio' ).prop( 'checked', true );
			}
		} );

		//If only one price then that one is default
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

		$( 'body' ).on( 'cmb2_add_row', function ( event, row ) {

			//Get the row ID and add 1 (iterator starts at 0 in CMB2)
			var row_id = $( row ).data( 'iterator' ) + 1;

			//Add row ID value to hidden field
			$( row ).find( 'input.give-hidden' ).val( row_id );
			//Add row ID to displayed ID
			$( row ).find( '.give-level-id' ).text( row_id );

		} );
		$( 'body' ).on( 'cmb2_shift_rows_complete', function ( event, self ) {

			var row_group = $( '.cmb-repeatable-grouping' );
			row_group.each( function ( index, object ) {


				var row_id = $( object ).find( 'input.give-level-id-input' ).val();

				$( object ).find( '.give-level-id' ).text( row_id );


			} );


		} );

		//$( 'body' ).on( 'cmb2_shift_rows_start', function ( event, self ) {
		//
		//
		//	//Get the Row ID
		//	var row_group = $( self ).parents( '.cmb-repeatable-grouping' );
		//	var row_id = $( row_group ).find( 'input.give-level-id-input' ).val();
		//
		//	console.log( self );
		//
		//	//Determine if we are moving value up or down
		//	if ( $( self ).hasClass( 'move-up' ) ) {
		//
		//		$( row_group ).prev().find( '.give-level-id' ).text( row_id );
		//
		//	} else {
		//
		//		$( row_group ).next().find( '.give-level-id' ).text( row_id );
		//	}
		//
		//	//move it to the next row
		//
		//
		//} );

	};


	//On DOM Ready
	$( function () {


		handle_default_radio();
		toggle_conditional_form_fields();
		handle_repeatable_row_ID();

	} );


})( jQuery );
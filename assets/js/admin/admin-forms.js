/*!
 * Give Admin Forms JS
 *
 * @description: The Give Admin Forms scripts. Only enqueued on the give_forms CPT; used to validate fields, show/hide, and other functions
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2015, WordImpress
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

		$( 'body' ).on( 'change', 'input.donation-level-radio', function () {
			$( 'input.donation-level-radio' ).not( this ).prop( 'checked', false );
		} );

		//If only one price then that one is default
		if ( number_of_prices === 1 ) {
			default_radio.prop( 'checked', true );
		}

		//When a row is removed containing the default selection then revert default to first repeatable row
		$( 'body' ).on( 'cmb2_remove_row', function ( e ) {
			var repeatable_rows = $( '#_give_donation_levels_repeat > .cmb-repeatable-grouping' );
			if ( $( 'input.donation-level-radio' ).is( ':checked' ) === false ) {
				repeatable_rows.first().find( 'input.donation-level-radio' ).prop( 'checked', true );
			}
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
				//set price shows
				$( '.cmb2-id--give-set-price' ).show();
				$( '.cmb2-id--give-levels-header, .cmb2-id--give-levels-header + .cmb-repeat-group-wrap, .cmb2-id--give-display-style' ).hide(); //hide multi-val stuffs

			} else {
				//multi-value shows
				$( '.cmb2-id--give-set-price' ).hide();
				$( '.cmb2-id--give-levels-header, .cmb2-id--give-levels-header + .cmb-repeat-group-wrap, .cmb2-id--give-display-style' ).show(); //show set stuffs
			}
		} ).change();


		//Content Option
		var content_option = $( '#_give_content_option' );

		content_option.on( 'change', function () {

			if ( content_option.val() !== 'none' ) {
				$( '.cmb2-id--give-form-content' ).show();
			} else {
				$( '.cmb2-id--give-form-content' ).hide();
			}
		} ).change();

		//Terms Option
		var terms_option = $( '#_give_terms_option' );
		terms_option.on( 'change', function () {

			if ( terms_option.val() !== 'none' ) {
				$( '.cmb2-id--give-agree-label' ).show();
				$( '.cmb2-id--give-agree-text' ).show();
			} else {
				$( '.cmb2-id--give-agree-label' ).hide();
				$( '.cmb2-id--give-agree-text' ).hide();
			}
		} ).change();

		//Payment Display
		var payment_display_option = $( '#_give_payment_display' );
		payment_display_option.on( 'change', function () {
			if ( payment_display_option.val() === 'onpage' ) {
				$( '.cmb2-id--give-reveal-label' ).hide();
			} else {
				$( '.cmb2-id--give-reveal-label' ).show();
			}
		} ).change();

		//Custom Amount
		var custom_amount_option = $( '.cmb2-id--give-custom-amount input:radio' );
		custom_amount_option.on( 'change', function () {
			var custom_amount_option_val = $( '.cmb2-id--give-custom-amount input:radio:checked' ).val();
			if ( custom_amount_option_val === 'no' ) {
				$( '.cmb2-id--give-custom-amount-text' ).hide();
			} else {
				$( '.cmb2-id--give-custom-amount-text' ).show();
			}
		} ).change();

		//Goals
		var goal_option = $( '.cmb2-id--give-goal-option' );
		goal_option.on( 'change', function () {
			var goal_option = $( '.cmb2-id--give-goal-option input:radio:checked' ).val();
			if ( goal_option === 'no' ) {

				$( '.cmb2-id--give-set-goal' ).hide();
				$( '.cmb2-id--give-goal-color' ).hide();
			} else {
				$( '.cmb2-id--give-set-goal' ).show();
				$( '.cmb2-id--give-goal-color' ).show();
			}
		} ).change();

		//Offline Donations
		var offline_customization_option = $( '.cmb2-id--give-customize-offline-donations input:radio' );
		offline_customization_option.on( 'change', function () {
			var offline_customization_option_val = $( '.cmb2-id--give-customize-offline-donations input:radio:checked' ).val();
			if ( offline_customization_option_val === 'no' ) {
				$( '.cmb2-id--give-offline-checkout-notes' ).hide();
				$( '.cmb2-id--give-offline-donation-enable-billing-fields-single' ).hide();
				$( '.cmb2-id--give-offline-donation-subject' ).hide();
				$( '.cmb2-id--give-offline-donation-email' ).hide();
			} else {
				$( '.cmb2-id--give-offline-checkout-notes' ).show();
				$( '.cmb2-id--give-offline-donation-enable-billing-fields-single' ).show();
				$( '.cmb2-id--give-offline-donation-subject' ).show();
				$( '.cmb2-id--give-offline-donation-email' ).show();
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


		/**
		 * Set Row IDs
		 *
		 * @description: Sets values in the Multi-level donation repeatable field
		 * @param row
		 */
		function set_row_ids( row ) {

			var row_count = count_repeatable_rows();

			//Add row ID value to hidden field
			$( row ).find( 'input.give-level-id-input' ).val( row_count );
			//Add row ID to displayed ID
			$( row ).find( '.give-level-id' ).text( row_count );

		}

		/**
		 * Loops through Multi-level repeater rows
		 *
		 * @description: First counts the rows then it compares the row count with the largest iterator count.
		 *
		 * @returns {number}
		 */
		function count_repeatable_rows() {
			var row_counter = 0;
			var row_largest_number = 0;
			var row_number = 0;

			//Loop through repeatable rows to see what highest ID is currently
			$( '#_give_donation_levels_repeat > .cmb-repeatable-grouping' ).each( function ( index, value ) {

				row_number = $( this ).find( 'input.give-level-id-input' ).val();

				if ( row_number > row_largest_number ) {
					row_largest_number = row_number;
				}

				row_counter++;

			} );

			if ( typeof row_largest_number !== 'undefined' && row_largest_number >= row_counter ) {
				return (parseInt( row_largest_number ) + 1); //ensure no duplicate rows returned
			} else {
				return row_counter;
			}

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
/*!
 * Give Admin JS
 *
 * @description: The Give Admin scripts
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2014, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ( $ ) {

	//--------------------------------------
	// Single Forms
	//--------------------------------------

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
			console.log( 'here' );
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


	/**
	 * Setup Admin Datepicker
	 * @since: 1.0
	 */
	var enable_admin_datepicker = function () {
		// Date picker
		if ( $( '.give_datepicker' ).length > 0 ) {
			var dateFormat = 'mm/dd/yy';
			$( '.give_datepicker' ).datepicker( {
				dateFormat: dateFormat
			} );
		}
	};


	/**
	 * Setup Pretty Chosen Select Fields
	 */
	var setup_chosen_give_selects = function () {
		// Setup Chosen Selects
		$( '.give-select-chosen' ).chosen( {
			inherit_select_classes   : true,
			placeholder_text_single  : give_vars.one_option,
			placeholder_text_multiple: give_vars.one_or_more_option
		} );

		// This fixes the Chosen box being 0px wide when the thickbox is opened
		$( '#post' ).on( 'click', '.give-thickbox', function () {
			$( '.give-select-chosen', '#choose-give-form' ).css( 'width', '100%' );
		} );

	};


	/**
	 * Edit payment screen JS
	 */
	var Give_Edit_Payment = {

		init: function () {
			this.edit_address();
			this.recalculate_total();
			this.add_note();
			this.remove_note();
			this.resend_receipt();
		},


		edit_address: function () {

			// Update base state field based on selected base country
			$( 'select[name="give-payment-address[0][country]"]' ).change( function () {
				var $this = $( this );
				data = {
					action    : 'give_get_shop_states',
					country   : $this.val(),
					field_name: 'give-payment-address[0][state]'
				};
				$.post( ajaxurl, data, function ( response ) {
					if ( 'nostates' == response ) {
						$( '#give-order-address-state-wrap select, #give-order-address-state-wrap input' ).replaceWith( '<input type="text" name="give-payment-address[0][state]" value="" class="give-edit-toggles medium-text"/>' );
					} else {
						$( '#give-order-address-state-wrap select, #give-order-address-state-wrap input' ).replaceWith( response );
					}
				} );

				return false;
			} );

		},

		recalculate_total: function () {

			// Remove a download from a purchase
			$( '#give-order-recalc-total' ).on( 'click', function ( e ) {
				e.preventDefault();
				var total = 0;
				if ( $( '#give-purchased-files .row .give-payment-details-download-amount' ).length ) {
					$( '#give-purchased-files .row .give-payment-details-download-amount' ).each( function () {
						total += parseFloat( $( this ).val() );
					} );
				}
				if ( $( '.give-payment-fees' ).length ) {
					$( '.give-payment-fees span.fee-amount' ).each( function () {
						total += parseFloat( $( this ).data( 'fee' ) );
					} );
				}
				$( 'input[name=give-payment-total]' ).val( total );
			} );

		},

		add_note: function () {

			$( '#give-add-payment-note' ).on( 'click', function ( e ) {
				e.preventDefault();
				var postData = {
					action    : 'give_insert_payment_note',
					payment_id: $( this ).data( 'payment-id' ),
					note      : $( '#give-payment-note' ).val()
				};

				if ( postData.note ) {

					$.ajax( {
						type   : "POST",
						data   : postData,
						url    : ajaxurl,
						success: function ( response ) {
							$( '#give-payment-notes-inner' ).append( response );
							$( '.give-no-payment-notes' ).hide();
							$( '#give-payment-note' ).val( '' );
						}
					} ).fail( function ( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					} );

				} else {
					var border_color = $( '#give-payment-note' ).css( 'border-color' );
					$( '#give-payment-note' ).css( 'border-color', 'red' );
					setTimeout( function () {
						$( '#give-payment-note' ).css( 'border-color', border_color );
					}, 500 );
				}

			} );

		},

		remove_note: function () {

			$( 'body' ).on( 'click', '.give-delete-payment-note', function ( e ) {

				e.preventDefault();

				if ( confirm( give_vars.delete_payment_note ) ) {

					var postData = {
						action    : 'give_delete_payment_note',
						payment_id: $( this ).data( 'payment-id' ),
						note_id   : $( this ).data( 'note-id' )
					};

					$.ajax( {
						type   : "POST",
						data   : postData,
						url    : ajaxurl,
						success: function ( response ) {
							$( '#give-payment-note-' + postData.note_id ).remove();
							if ( !$( '.give-payment-note' ).length ) {
								$( '.give-no-payment-notes' ).show();
							}
							return false;
						}
					} ).fail( function ( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					} );
					return true;
				}

			} );

		},

		resend_receipt: function () {
			$( 'body' ).on( 'click', '#give-resend-receipt', function ( e ) {
				return confirm( give_vars.resend_receipt );
			} );
		}

	};


	//On DOM Ready
	$( function () {

		handle_default_radio();
		toggle_conditional_form_fields();
		enable_admin_datepicker();
		setup_chosen_give_selects();
		Give_Edit_Payment.init();

	} );


})( jQuery );
/*!
 * Give Admin JS
 *
 * @description: The Give Admin scripts
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ( $ ) {

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
			this.add_note();
			this.remove_note();
			this.new_donor();
			this.resend_receipt();
		},


		edit_address: function () {

			// Update base state field based on selected base country
			$( 'select[name="give-payment-address[0][country]"]' ).change( function () {
				var $this = $( this );
				data = {
					action    : 'give_get_states',
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

		remove_note   : function () {

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
		new_donor     : function () {

			$( '#give-customer-details' ).on( 'click', '.give-payment-new-customer, .give-payment-new-customer-cancel', function ( e ) {
				e.preventDefault();
				$( '.customer-info' ).toggle();
				$( '.new-customer' ).toggle();

				if ( $( '.new-customer' ).is( ":visible" ) ) {
					$( '#give-new-customer' ).val( 1 );
				} else {
					$( '#give-new-customer' ).val( 0 );
				}

			} );

		},
		resend_receipt: function () {
			$( 'body' ).on( 'click', '#give-resend-receipt', function ( e ) {
				return confirm( give_vars.resend_receipt );
			} );
		}

	};


	/**
	 * Settings screen JS
	 */
	var Give_Settings = {

		init: function () {
			this.toggle_options();
		},

        toggle_options: function () {

            var email_access = $('#email_access');
            email_access.on('change', function(){
                if ( email_access.prop('checked') ) {
                    $( '.cmb2-id-recaptcha-key, .cmb2-id-recaptcha-secret' ).show();
                } else {
                    $( '.cmb2-id-recaptcha-key, .cmb2-id-recaptcha-secret' ).hide();
                }
            } ).change();
        },

	};

    /**
     * Reports / Exports screen JS
     */
    var Give_Reports = {

        init: function () {
            this.date_options();
            this.donors_export();
        },

        date_options: function () {

            // Show hide extended date options
            $('#give-graphs-date-options').change(function () {
                var $this = $(this);
                if ('other' === $this.val()) {
                    $('#give-date-range-options').show();
                } else {
                    $('#give-date-range-options').hide();
                }
            });

        },

        donors_export: function () {

            // Show / hide Download option when exporting donors
            $('#give_donor_export_download').change(function () {

                var $this = $(this), form_id = $('option:selected', $this).val();

                if ('0' === $this.val()) {
                    $('#give_donor_export_option').show();
                } else {
                    $('#give_donor_export_option').hide();
                }

                $('.give_price_options_select').remove();

            });

        }

    };


	/**
	 * Admin Status Select Field Change
	 *
	 * @description: Handle status switching
	 * @since: 1.0
	 */
	var handle_status_change = function () {

		//When sta
		$( 'select[name="give-payment-status"]' ).on( 'change', function () {

			var status = $( this ).val();
			console.log( status );
			$( '.give-donation-status' ).removeClass( function ( index, css ) {
				return (css.match( /\bstatus-\S+/g ) || []).join( ' ' );
			} ).addClass( 'status-' + status );


		} );

	};

	/**
	 * Customer management screen JS
	 */
	var Give_Customer = {

		init          : function () {
			this.edit_customer();
			this.user_search();
			this.remove_user();
			this.cancel_edit();
			this.change_country();
			this.add_note();
			this.delete_checked();
		},
		edit_customer : function () {
			$( 'body' ).on( 'click', '#edit-customer', function ( e ) {
				e.preventDefault();
				$( '#give-customer-card-wrapper .editable' ).hide();
				$( '#give-customer-card-wrapper .edit-item' ).fadeIn().css( 'display', 'block' );
			} );
		},
		user_search   : function () {
			// Upon selecting a user from the dropdown, we need to update the User ID
			$( 'body' ).on( 'click.giveSelectUser', '.give_user_search_results a', function ( e ) {
				e.preventDefault();
				var user_id = $( this ).data( 'userid' );
				$( 'input[name="customerinfo[user_id]"]' ).val( user_id );
			} );
		},
		remove_user   : function () {
			$( 'body' ).on( 'click', '#disconnect-customer', function ( e ) {
				e.preventDefault();
				var customer_id = $( 'input[name="customerinfo[id]"]' ).val();

				var postData = {
					give_action: 'disconnect-userid',
					customer_id: customer_id,
					_wpnonce   : $( '#edit-customer-info #_wpnonce' ).val()
				};

				$.post( ajaxurl, postData, function ( response ) {

					window.location.href = window.location.href;

				}, 'json' );

			} );
		},
		cancel_edit   : function () {
			$( 'body' ).on( 'click', '#give-edit-customer-cancel', function ( e ) {
				e.preventDefault();
				$( '#give-customer-card-wrapper .edit-item' ).hide();
				$( '#give-customer-card-wrapper .editable' ).show();
				$( '.give_user_search_results' ).html( '' );
			} );
		},
		change_country: function () {
			$( 'select[name="customerinfo[country]"]' ).change( function () {
				var $this = $( this );
				var data = {
					action    : 'give_get_states',
					country   : $this.val(),
					field_name: 'customerinfo[state]'
				};
				$.post( ajaxurl, data, function ( response ) {
					if ( 'nostates' == response ) {
						$( ':input[name="customerinfo[state]"]' ).replaceWith( '<input type="text" name="' + data.field_name + '" value="" class="give-edit-toggles medium-text"/>' );
					} else {
						$( ':input[name="customerinfo[state]"]' ).replaceWith( response );
					}
				} );

				return false;
			} );
		},
		add_note      : function () {
			$( 'body' ).on( 'click', '#add-customer-note', function ( e ) {
				e.preventDefault();
				var postData = {
					give_action            : 'add-customer-note',
					customer_id            : $( '#customer-id' ).val(),
					customer_note          : $( '#customer-note' ).val(),
					add_customer_note_nonce: $( '#add_customer_note_nonce' ).val()
				};

				if ( postData.customer_note ) {

					$.ajax( {
						type   : "POST",
						data   : postData,
						url    : ajaxurl,
						success: function ( response ) {
							$( '#give-customer-notes' ).prepend( response );
							$( '.give-no-customer-notes' ).hide();
							$( '#customer-note' ).val( '' );
						}
					} ).fail( function ( data ) {
						if ( window.console && window.console.log ) {
							console.log( data );
						}
					} );

				} else {
					var border_color = $( '#customer-note' ).css( 'border-color' );
					$( '#customer-note' ).css( 'border-color', 'red' );
					setTimeout( function () {
						$( '#customer-note' ).css( 'border-color', border_color );
					}, 500 );
				}
			} );
		},
		delete_checked: function () {
			$( '#give-customer-delete-confirm' ).change( function () {
				var records_input = $( '#give-customer-delete-records' );
				var submit_button = $( '#give-delete-customer' );

				if ( $( this ).prop( 'checked' ) ) {
					records_input.attr( 'disabled', false );
					submit_button.attr( 'disabled', false );
				} else {
					records_input.attr( 'disabled', true );
					records_input.prop( 'checked', false );
					submit_button.attr( 'disabled', true );
				}
			} );
		}

	};
	/**
	 * API screen JS
	 */
	var API_Screen = {

		init: function () {
			this.revoke_api_key();
			this.regenerate_api_key();
		},

		revoke_api_key    : function () {
			$( 'body' ).on( 'click', '.give-revoke-api-key', function ( e ) {
				return confirm( give_vars.revoke_api_key );
			} );
		},
		regenerate_api_key: function () {
			$( 'body' ).on( 'click', '.give-regenerate-api-key', function ( e ) {
				return confirm( give_vars.regenerate_api_key );
			} );
		}
	};

	/**
	 * Initialize qTips
	 */
	var initialize_qtips = function () {
		jQuery( '[data-tooltip!=""]' ).qtip( { // Grab all elements with a non-blank data-tooltip attr.
			content: {
				attr: 'data-tooltip' // Tell qTip2 to look inside this attr for its content
			},
			style  : {classes: 'qtip-rounded qtip-tipsy'},
			events : {
				show: function ( event, api ) {
					var $el = $( api.elements.target[0] );
					$el.qtip( 'option', 'position.my', ($el.data( 'tooltip-my-position' ) == undefined) ? 'bottom center' : $el.data( 'tooltip-my-position' ) );
					$el.qtip( 'option', 'position.at', ($el.data( 'tooltip-target-position' ) == undefined) ? 'top center' : $el.data( 'tooltip-target-position' ) );
				}
			}
		} )
	};

	//On DOM Ready
	$( function () {

		enable_admin_datepicker();
		handle_status_change();
		setup_chosen_give_selects();
		Give_Edit_Payment.init();
		Give_Settings.init();
		Give_Reports.init();
		Give_Customer.init();
		API_Screen.init();
		initialize_qtips();

		//Footer
		$( 'a.give-rating-link' ).click( function () {
			jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
		} );

		// Ajax user search
		$( '.give-ajax-user-search' ).on( 'keyup', function () {
			var user_search = $( this ).val();
			var exclude = '';

			if ( $( this ).data( 'exclude' ) ) {
				exclude = $( this ).data( 'exclude' );
			}

			$( '.give-ajax' ).show();
			data = {
				action   : 'give_search_users',
				user_name: user_search,
				exclude  : exclude
			};

			document.body.style.cursor = 'wait';

			$.ajax( {
				type    : "POST",
				data    : data,
				dataType: "json",
				url     : ajaxurl,
				success : function ( search_response ) {
					$( '.give-ajax' ).hide();
					$( '.give_user_search_results' ).removeClass( 'hidden' );
					$( '.give_user_search_results span' ).html( '' );
					$( search_response.results ).appendTo( '.give_user_search_results span' );
					document.body.style.cursor = 'default';
				}
			} );
		} );

		$( 'body' ).on( 'click.giveSelectUser', '.give_user_search_results span a', function ( e ) {
			e.preventDefault();
			var login = $( this ).data( 'login' );
			$( '.give-ajax-user-search' ).val( login );
			$( '.give_user_search_results' ).addClass( 'hidden' );
			$( '.give_user_search_results span' ).html( '' );
		} );

		$( 'body' ).on( 'click.giveCancelUserSearch', '.give_user_search_results a.give-ajax-user-cancel', function ( e ) {
			e.preventDefault();
			$( '.give-ajax-user-search' ).val( '' );
			$( '.give_user_search_results' ).addClass( 'hidden' );
			$( '.give_user_search_results span' ).html( '' );
		} );

	} );


})( jQuery );
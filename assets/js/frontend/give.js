/*!
 * Give JS
 *
 * @description: Scripts that power the Give experience
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2015, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
var give_scripts;
jQuery( document ).ready( function ( $ ) {

	var $body = $( 'body' );

	// Reveal Btn which displays the checkout content
	$body.on( 'click', '.give-btn-reveal', function ( e ) {
		e.preventDefault();
		var this_button = $( this );
		var this_form = $( this ).parents( 'form' );
		this_button.hide();
		this_form.find( '#give-payment-mode-select, #give_purchase_form_wrap' ).slideDown();
		return false;
	} );

	// Modal with Magnific
	$body.on( 'click', '.give-btn-modal', function ( e ) {
		e.preventDefault();
		var this_form = $( this ).parents( 'div.give-form-wrap' );
		var this_amount_field = this_form.find( '#give-amount' );
		var this_amount = this_amount_field.val();
		//Check to ensure our amount is greater than 0

		//Does this number have a value
		if ( !this_amount || this_amount <= 0 ) {
			this_amount_field.focus();
			return false;
		}

		//Alls well, open popup!
		$.magnificPopup.open( {
			mainClass: 'give-modal',
			items    : {
				src : this_form,
				type: 'inline'
			},
			callbacks: {
				open : function () {
					// Will fire when this exact popup is opened
					// this - is Magnific Popup object
					if ( $( '.mfp-content' ).outerWidth() >= 500 ) {

						$( '.mfp-content' ).addClass( 'give-responsive-mfp-content' );

					}

				},
				close: function () {
					//Remove popup class
					this_form.removeClass( 'mfp-hide' );
				}
			}
		} );

	} );

} );

function customLabels( el )
{
	var id = el.attr( 'id' );

	// todo: multilingual label vars for month/year

	if( id === 'card_number' ) {
		el.after( '<span class="off card-type"/>' );
	}

	if( id === 'card_exp_month' ) {
		el.parent().addClass( id );
		return 'Month';
	}

	if( id === 'card_exp_year' ) {
		el.parent().addClass( id );
		return 'Year';
	}

	if( el.hasClass( 'give-select-level' ) ) {
		return 'Donation Amount';
	}
}

jQuery( function( $ )
{
	if( give_scripts.floatlabels === '1' ) {

		var doc     = $( document ),
			options = {
				exclude: ['#give-amount'],
				customLabel: customLabels
			};

		$( '.give-form' ).floatlabels( options );

		doc.on( 'give_gateway_loaded', function()
		{
			$( '.give-form' ).floatlabels( options );
		});

		doc.on( 'give_checkout_billing_address_updated', function( ev, response )
		{
			var wrap  = $( '#give-card-state-wrap' ),
				el    = wrap.find( '#card_state' ),
				label = wrap.find( 'label[for="card_state"]' );

			label = label.length ? label.text().replace( '*', '' ).trim() : '';

			if( 'nostates' === response ) {
				// fix input
				el.attr( 'placeholder', label ).parent().removeClass( 'styled select' );
			}
			else {
				// fix select
				el.children().first().text( label );
				el.parent().addClass( 'styled select' );
			}

			el.parent().removeClass( 'is-active' );

			$( '.give-form' ).floatlabels( options );
		});
	}
});

/* globals jQuery, give_global_vars */
import '../plugins/dynamicListener.js';

/**
 * This class handles donor wall shortcode related features
 *
 * @since 2.2.0
 *
 */
class GiveDonorWall {
	constructor() {
		var gravatar = require('gravatar');

		window.addEventListener(
			'load', function() {
				/**
				 * Add events
				 */
				window.addDynamicEventListener( document, 'click', '.give-donor__read-more', GiveDonorWall.readMoreBtnEvent );
				window.addDynamicEventListener( document, 'click', '.give-donor__load_more', GiveDonorWall.loadMoreBtnEvent );
				/**
				 * Loop through the number of donor list on the page.
				 *
				 * @since 2.3.0
				 *
				 */
				const gridWraps = document.querySelectorAll( '.give-grid__item' );
				Array.prototype.forEach.call( gridWraps, function( gridWraps ) {
					const donor_image_element = gridWraps.querySelector( '.give-donor__image' );
					let donor_email = donor_image_element.getAttribute( 'data-donor_email' );
					let donor_avatar_attr = donor_image_element.getAttribute( 'data-donor_avatar_attr' );
					if ( '1' === donor_avatar_attr ) {
						jQuery( donor_image_element ).html( '' );
						const donor_avatar_element = document.createElement( 'IMG' );
						donor_avatar_element.setAttribute( 'src', gravatar.url( donor_email ) );
						donor_avatar_element.setAttribute( 'width', '60' );
						donor_avatar_element.setAttribute( 'height', '60' );
						donor_image_element.appendChild( donor_avatar_element );
					}

				} );
			}, false
		);


	}

	/**
	 * Add click event to read more link
	 *
	 * @since  2.2.0
	 *
	 * @param {object} evt
	 */
	static readMoreBtnEvent(evt) {
		evt.preventDefault();

		jQuery.magnificPopup.open(
			{
				items: {
					src: evt.target.parentNode.parentNode.parentNode.parentNode.getElementsByClassName('give-donor__comment')[0].innerHTML,
					type: 'inline',
				},
				mainClass: 'give-modal give-donor-wall-modal',
				closeOnBgClick: false,
			}
		);

		return false;
	}

	/**
	 * Add click event to load more link
	 *
	 * @since  2.2.0
	 *
	 * @param {object} evt
	 */
	static loadMoreBtnEvent(evt) {
		evt.preventDefault();

		jQuery.ajax({
			url: Give.fn.getGlobalVar( 'ajaxurl' ),
			method: 'POST',
			data: {
				action: 'give_get_donor_comments',
				data: evt.target.getAttribute('data-shortcode')
			},
			beforeSend(){
				evt.target.className += ' give-active';
				evt.target.setAttribute('disabled', 'disabled' );
			}
		}).done(function (res) {
			evt.target.classList.remove('give-active');
			evt.target.removeAttribute('disabled', 'disabled' );

			// Add donor comment.
			if (res.html.length) {
				evt.target
					.parentNode
					.getElementsByClassName('give-grid')[0]
					.insertAdjacentHTML('beforeend', res.html);
			}

			// Update data-shortcode attribute.
			if( res.shortcode.length ){
				evt.target.setAttribute('data-shortcode', res.shortcode );
			}

			// Remove load more button if not any donor comment exist.
			if (!res.remaining) {
				evt.target.remove();
			}
		});

		return false;
	}
}

let giveDonorWall = new GiveDonorWall();

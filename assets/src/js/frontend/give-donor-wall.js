/* globals jQuery, Give, give_global_vars */
import '../plugins/dynamicListener.js';

/**
 * This class handles donor wall shortcode related features
 *
 * @since 2.2.0
 *
 */
class GiveDonorWall {
	constructor() {
		window.addEventListener(
			'load', function () {
				/**
				 * Add events
				 */
				window.addDynamicEventListener(document, 'click', '.give-donor__read-more', GiveDonorWall.readMoreBtnEvent);
				window.addDynamicEventListener(document, 'click', '.give-donor__load_more', GiveDonorWall.loadMoreBtnEvent);

			}, false
		);

		GiveDonorWall.loadGravatar();
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
			url: Give.fn.getGlobalVar('ajaxurl'),
			method: 'POST',
			data: {
				action: 'give_get_donor_comments',
				data: evt.target.getAttribute('data-shortcode')
			},
			beforeSend() {
				evt.target.className += ' give-active';
				evt.target.setAttribute('disabled', 'disabled');
			}
		}).done(function (res) {
			evt.target.classList.remove('give-active');
			evt.target.removeAttribute('disabled', 'disabled');

			// Add donor comment.
			if (res.html.length) {
				evt.target
					.parentNode
					.getElementsByClassName('give-grid')[0]
					.insertAdjacentHTML('beforeend', res.html);
			}

			// Update data-shortcode attribute.
			if (res.shortcode.length) {
				evt.target.setAttribute('data-shortcode', res.shortcode);
			}

			// Remove load more button if not any donor comment exist.
			if (!res.remaining) {
				evt.target.remove();
			}

			GiveDonorWall.loadGravatar();
		});

		return false;
	}

	/**
	 * Handle gravatar loading
	 *
	 * @since 2.3.0
	 */
	static loadGravatar() {
		const gravatar = require('gravatar');

		/**
		 * Loop through the number of donor list on the page.
		 *
		 * @since 2.3.0
		 *
		 */
		let gridWraps = document.querySelectorAll('.give-grid__item'),
			gravatarContainer,
			donorEmail,
			isShowGravatar,
			hasValidGravatar;

		gridWraps.forEach(function (gridWrap, index) {
			gravatarContainer = gridWrap.querySelector('.give-donor__image');

			// Bailout out if already loaded gravatar.
			if (gravatarContainer.classList.contains('gravatar-loaded')) {
				return;
			}

			donorEmail       = gravatarContainer.getAttribute('data-donor_email');
			isShowGravatar   = gravatarContainer.getAttribute('data-donor_avatar_attr');
			hasValidGravatar = gravatarContainer.getAttribute('data-has-valid-gravatar');

			if ('1' === isShowGravatar && '1' === hasValidGravatar) {
				let gravatarElement = document.createElement('IMG');

				gravatarContainer.innerHTML = '';
				gravatarElement.setAttribute('src', gravatar.url(donorEmail));
				gravatarElement.setAttribute('width', '60');
				gravatarElement.setAttribute('height', '60');
				gravatarContainer.appendChild(gravatarElement);

				gravatarContainer.className += ' gravatar-loaded';
			}

		});
	}
}

let giveDonorWall = new GiveDonorWall();

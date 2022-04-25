/* globals Give, jQuery, commonL10n */
import {__} from '@wordpress/i18n';

export default {
	fn: {
		/**
		 * Render notice
		 * @since 1.8.17
		 *
		 * @param {string} notice_code
		 * @param {object} $container
		 *
		 * @return {string}
		 */
		renderNotice: function( notice_code, $container ) {
			let notice_html = '',
				$notice;
			$container = 'undefined' !== typeof $container ? $container : {};

			switch ( notice_code ) {
				case 'bad_minimum':
					$notice = jQuery(
						'<div class="give_error give-invalid-minimum give-hidden">' +
						this.getNotice( notice_code, $container ) +
						'</div>'
					);
					break;
				case 'bad_maximum':
					$notice = jQuery(
						'<div class="give_error give-invalid-maximum give-hidden">' +
						this.getNotice( notice_code, $container ) +
						'</div>'
					);
					break;
			}

			// Return html if container did not find.
			if ( ! $container.length ) {
				return notice_html;
			}

			$notice.insertBefore( $container.find( '.give-total-wrap' ) ).show();
		},

		/**
		 * Get error notice
		 *
		 * @since 1.8.17
		 * @param {string} error_code
		 * @param {object} $form
		 *
		 * @return {*}
		 */
		getNotice: function( error_code, $form ) {
			// Bailout.
			if ( ! error_code.length ) {
				return null;
			}

			let notice, notice_msg, formatted_amount;
			notice = notice_msg = formatted_amount = '';

			if ( $form.length ) {
				switch ( error_code ) {
					case 'bad_minimum':
						notice_msg = Give.fn.getGlobalVar( error_code );
						formatted_amount = Give.form.fn.getMinimumAmount( $form );
						break;
					case 'bad_maximum':
						notice_msg = Give.fn.getGlobalVar( error_code );
						formatted_amount = Give.form.fn.getMaximumAmount( $form );
						break;
				}
			}

			if ( $form.length && '' !== notice_msg ) {
				notice = notice_msg + ' ' + Give.fn.formatCurrency(
					formatted_amount,
					{
						symbol: Give.form.fn.getInfo( 'currency_symbol', $form ),
						position: Give.form.fn.getInfo( 'currency_position', $form ),
					},
					$form
				);
			}

			return notice;
		},

		/**
		 * Print notice
		 * Note: use only in WP Backend
		 *
		 * @since 2.5.0
		 * @since 2.8.0 Localization updated to support changes in WordPress 5.5.
		 *
		 * @param {string} notice Notice description.
		 * @param {string} type   Notice type.
		 * @param {object} args   Notice type.
		 *
		 * @return {string} Notice HTML.
		 */
		getAdminNoticeHTML: function( notice, type = 'info', args = { dismissible: true } ) {
			/**
			 * WordPress 5.5 removed the localized `commonL10n` in favor of translation in JavaScript.
			 */
			const btnText = ( 'undefined' !== typeof commonL10n ) ?
				commonL10n.dismiss :
				__( 'Dismiss this notice.', 'give' );

			return `<div class="give-notice notice notice-${ type }${ args.dismissible ? ' is-dismissible' : '' }"><p>${ notice }${ args.dismissible ? ` <button type="button" class="notice-dismiss"><span class="screen-reader-text">${ btnText }</span></button>` : '' }</p</div>`;
		},
	},
};

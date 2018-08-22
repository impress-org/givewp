/* globals Give, jQuery */
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

			var notice, notice_msg, formatted_amount;
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
						position: Give.form.fn.getInfo( 'currency_position', $form )
					},
					$form
				);
			}

			return notice;
		}
	}
};

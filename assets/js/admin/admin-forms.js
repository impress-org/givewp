/*!
 * Give Admin Forms JS
 *
 * @description: The Give Admin Forms scripts. Only enqueued on the give_forms CPT; used to validate fields, show/hide, and other functions
 * @package:     Give
 * @subpackage:  Assets/JS
 * @copyright:   Copyright (c) 2016, WordImpress
 * @license:     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

jQuery.noConflict();
(function ($) {
	/**
	 * Default Radio Button
	 *
	 * @description: Allow only one radio button to be checked at a time
	 * @since: 1.0
	 */
	var handle_default_radio = function () {
		$('body').on( 'change', '.give-give_default_radio_inline', function(){
			// Unset pre selected default level.
			$('.give-give_default_radio_inline').prop( 'checked', false );

			// Set level as default.
			$(this).prop( 'checked', true );
		});

		// Note: some cases is covered in admin-scripts.js.
		// For ref: handle_metabox_repeater_field_row_count, handle_metabox_repeater_field_row_remove
	};


	/**
	 * Toggle Conditional Form Fields
	 *
	 *  @since: 1.0
	 */
	var toggle_conditional_form_fields = function () {

		//Price Option
		var price_option = $('._give_price_option_field input:radio');

		price_option.on('change', function () {

			var price_option_val = $('._give_price_option_field input:radio:checked').val();
			if (price_option_val === 'set') {
				//set price shows
				$('._give_set_price_field').show();
				$('#_give_donation_levels_field').hide(); // Hide multi-val stuffs.
				$('._give_display_style_field').hide(); // Hide display style setting.


			} else {
				//multi-value shows
				$('._give_set_price_field').hide();
				$('#_give_donation_levels_field').show(); // Show set stuffs.
				$('._give_display_style_field').show(); // Show display style setting.
			}
		}).change();


		//Content Option
		var  display_content = $('._give_display_content_field input:radio');
		display_content.on('change', function () {
			// Get checked radio button value.
			var display_content_val = $('._give_display_content_field input:radio:checked').val();

			if ( display_content_val === 'enabled') {
				$('._give_content_placement_field').show();
				$('._give_form_content_field').show();
			} else {
				$('._give_content_placement_field').hide();
				$('._give_form_content_field').hide();
			}
		}).change();

		//Terms Option
		var terms_option = $('._give_terms_option_field input:radio');
		terms_option.on('change', function () {
			// Get checked radio button value.
			var terms_option_val = $('._give_terms_option_field input:radio:checked').val();

			if ( terms_option_val === 'enabled' ) {
				$('._give_agree_label_field').show();
				$('._give_agree_text_field').show();
			} else {
				$('._give_agree_label_field').hide();
				$('._give_agree_text_field').hide();
			}
		}).change();

		//Payment Display
		var payment_display_option = $('._give_payment_display_field input:radio');
		payment_display_option.on('change', function () {
			var payment_display_option_val = $('._give_payment_display_field input:radio:checked').val();

			if (payment_display_option_val === 'onpage') {
				$('._give_reveal_label_field').hide();
			} else {
				$('._give_reveal_label_field').show();
			}
		}).change();

		//Custom Amount
		var custom_amount_option = $('._give_custom_amount_field input:radio');
		custom_amount_option.on('change', function () {
			var custom_amount_option_val = $('._give_custom_amount_field input:radio:checked').val();
			if (custom_amount_option_val === 'disabled') {
				$('._give_custom_amount_minimum_field').hide();
				$('._give_custom_amount_text_field').hide();
			} else {
				$('._give_custom_amount_minimum_field').show();
				$('._give_custom_amount_text_field').show();
			}
		}).change();

		//Goals
		var goal_option = $('._give_goal_option_field');
		//Close Form when Goal Achieved
		var close_form_when_goal_achieved_option = $('._give_close_form_when_goal_achieved_field input:radio');

		close_form_when_goal_achieved_option.on('change', function () {
			var close_form_when_goal_achieved_option_val = $('._give_close_form_when_goal_achieved_field input:radio:checked').val();
			if (close_form_when_goal_achieved_option_val === 'disabled') {
				$('._give_form_goal_achieved_message_field').hide();
			} else {
				$('._give_form_goal_achieved_message_field').show();
			}
		}).change();

		goal_option.on('change', function () {
			var goal_option = $('._give_goal_option_field input:radio:checked').val();
			if (goal_option === 'disabled') {

				$('._give_set_goal_field').hide();
				$('._give_goal_format_field').hide();
				$('._give_goal_color_field').hide();
				$('._give_close_form_when_goal_achieved_field').hide();
				$('._give_form_goal_achieved_message_field').hide();
				$('._give_number_of_donation_goal_field').hide();
			} else {
				$('._give_set_goal_field').show();
				$('._give_goal_format_field').show();
				$('._give_goal_color_field').show();
				$('._give_close_form_when_goal_achieved_field').show();

				var close_form_when_goal_achieved_option_val = $('._give_close_form_when_goal_achieved_field input:radio:checked').val();

				if (close_form_when_goal_achieved_option_val === 'enabled') {
					$('._give_form_goal_achieved_message_field').show();
				}

			}
		}).change();

		var goal_format = $('._give_goal_format_field input:radio');
		goal_format.on('change', function() {
			var goal_format_val = $('._give_goal_format_field input:radio:checked').val();
			var goal_option_val = $('._give_goal_option_field input:radio:checked').val();

			if( 'donation' === goal_format_val ) {
				$('._give_set_goal_field').hide();
				$('._give_number_of_donation_goal_field').show();
			} else {
				( 'disabled' === goal_option_val ) ? $('._give_set_goal_field').hide() : $('._give_set_goal_field').show();
				$('._give_number_of_donation_goal_field').hide();
			}
		}).change();

		//Offline Donations
		var offline_customization_option = $('._give_customize_offline_donations_field input:radio');
		offline_customization_option.on('change', function () {
			var offline_customization_option_val = $('._give_customize_offline_donations_field input:radio:checked').val();
			if ( 'enabled' === offline_customization_option_val ) {
				$('._give_offline_checkout_notes_field').show();
				$('._give_offline_donation_enable_billing_fields_single_field').show();
				$('._give_offline_donation_subject_field').show();
				$('._give_offline_donation_email_field').show();
			} else {
				$('._give_offline_checkout_notes_field').hide();
				$('._give_offline_donation_enable_billing_fields_single_field').hide();
				$('._give_offline_donation_subject_field').hide();
				$('._give_offline_donation_email_field').hide();
			}
		}).change();

		//Email options.
		var  email_options = $('._give_email_options_field input:radio');
		email_options.on('change', function () {
			// Get checked radio button value.
			var email_options_val = $('._give_email_options_field input:radio:checked').val();

			if ( email_options_val === 'enabled') {
				$('#email_notification_options .give-field-wrap:not(._give_email_options_field)').show();
			} else {
				$('#email_notification_options .give-field-wrap:not(._give_email_options_field)').hide();
			}
		}).change();
	};

	//Handle Repeatable Row ID
	var handle_repeatable_row_ID = function () {

		//Ensure for new posts that the repeater is filled
		if ($('.give-level-id').text() === '') {
			var row_group = $('.cmb-repeatable-grouping');
			//loop through all repeatable rows and set vals
			row_group.each(function (index, object) {

				var row_id = $(object).data('iterator') + 1;

				$(object).find('.give-level-id').text(row_id);
				$(object).find('.give-level-id-input').val(row_id);

			});
		}

		$('body').on('cmb2_add_row', function (event, row) {
			set_row_ids(row);
		});
		$('body').on('cmb2_shift_rows_complete', function (event, self) {

			var row_group = $('.cmb-repeatable-grouping');
			//loop through all repeatable rows and set vals
			row_group.each(function (index, object) {

				var row_id = $(object).find('input.give-level-id-input').val();

				$(object).find('.give-level-id').text(row_id);

			});

		});


		/**
		 * Set Row IDs
		 *
		 * @description: Sets values in the Multi-level donation repeatable field
		 * @param row
		 */
		function set_row_ids(row) {

			var row_count = count_repeatable_rows();

			//Add row ID value to hidden field
			$(row).find('input.give-level-id-input').val(row_count);
			//Add row ID to displayed ID
			$(row).find('.give-level-id').text(row_count);

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
			$('#_give_donation_levels_repeat > .cmb-repeatable-grouping').each(function (index, value) {

				row_number = $(this).find('input.give-level-id-input').val();

				if (row_number > row_largest_number) {
					row_largest_number = row_number;
				}

				row_counter++;

			});

			if (typeof row_largest_number !== 'undefined' && row_largest_number >= row_counter) {
				return (parseInt(row_largest_number) + 1); //ensure no duplicate rows returned
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
		$('.cmb2-text-money').each(function (index, object) {
			var this_val = parseInt($(object).val());
			if (!this_val) {
				$(object).removeAttr('value');
			}
		});

	}


	//On DOM Ready
	$(function () {

		handle_default_radio();
		toggle_conditional_form_fields();
		handle_repeatable_row_ID();
		misc_cleanup();

	});


})(jQuery);

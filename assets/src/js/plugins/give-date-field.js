/* globals jQuery, give_ffm_frontend, give_ffm_formbuilder */
import './jquery-ui-timepicker-addon';
const {__} = wp.i18n;

window.addEventListener('load', (event) => {
	const setDatePicker = () => {
		const allDateFields = document.querySelectorAll('[data-field-type="date"] input[type="text"]');

		if (!allDateFields) {
			return;
		}
		allDateFields.forEach((dateField) => {
			const $this = jQuery(dateField);

			// If field does not have [data-timeformat] attribute that mean it is a datepicker, otherwise datepicker + timepicker.
			if ( ! dateField.getAttribute('timeformat' ) ) {
				$this.datepicker();
				return;
			}

			const date = new Date();

			$this.datetimepicker({
				dateFormat: $this.data('dateformat'),
				timeFormat: $this.data('timeformat'),
				hour: date.getHours(),
				minute: date.getMinutes(),
				currentText: __('Now', 'give'),
				closeText: __('Done', 'give'),
				timeOnlyTitle: __('Choose Time', 'give'),
				timeText: __('Time', 'give'),
				hourText: __('Hour', 'give'),
				minuteText: __('Minute', 'give'),
			});
		})
	};

	setDatePicker();
});

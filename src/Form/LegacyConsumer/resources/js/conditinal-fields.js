window.addEventListener('load', () => {
	/**
	 * Handle fields visibility
	 * @unreleased
	 */
	function handleVisibility(donationForm) {
		donationForm.querySelectorAll('[data-field-visibility-conditions]').forEach(function (inputField) {
			const fieldWrapper = inputField.closest('.form-row');
			const visibilityConditions = JSON.parse(inputField.getAttribute('data-field-visibility-conditions'));
			const visibilityCondition = visibilityConditions[0]; // Currently we support only one visibility condition.
			let visible = false;
			const {field, value} = visibilityCondition;

			const inputs = donationForm.querySelectorAll(`[name="${field}"]`);
			let hasFieldController = !! inputs.length;

			// Do not apply visibility conditions if field controller does not exit in DOM.
			if ( ! hasFieldController ) {
				return;
			}

			inputs.forEach((input) => {
				const fieldType = input.getAttribute('type');

				if (fieldType && (fieldType === 'radio' || fieldType === 'checkbox')) {
					if (input.checked && input.value === value) {
						visible = true;
					}
				} else if (input.value === value) {
					visible = true;
				}
			});

			// Show or Hide field wrapper.
			visible ?
				fieldWrapper.classList.remove('give-hidden') :
				fieldWrapper.classList.add('give-hidden');
		});
	}

	/**
	 * @unreleased
	 * @param event
	 */
	function handleVisibilityOnChangeHandler(event) {
		handleVisibility(event.target.closest('.give-form') );
	}

	document.querySelectorAll('form.give-form')
		.forEach(function (donationForm) {
			handleVisibility(donationForm);
			donationForm.querySelectorAll('input, select, textarea')
				.forEach(field => field.addEventListener('change', handleVisibilityOnChangeHandler));
		});
});
// @todo: attach event when form reload on gateway switch.

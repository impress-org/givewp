document.addEventListener('readystatechange', event => {
	if (event.target.readyState !== 'complete') {
		return null;
	}

	const state = {};

	/**
	 * @since 2.15.0
	 *
	 * @param {HTMLElement} inputField
	 * @return {string}
	 */
	function getFieldSelector(inputField) {
		const container = inputField.closest('.form-row');
		let fieldSelector = '';

		if (inputField.name) {
			fieldSelector = inputField.name;
		} else if ('html' === container.getAttribute('data-field-type')) {
			fieldSelector = `[data-field-name="${container.getAttribute('data-field-name')}"]`;
		} else {
			fieldSelector = `[data-field-name="${container.getAttribute('data-field-name')}"] ${inputField.nodeName.toLowerCase()}`;
		}

		return fieldSelector;
	}

	/**
	 * Get list of watched fields.
	 * @since 2.15.0
	 *
	 * @return object
	 */
	function getWatchedElementNames(donationForm) {
		const fields = {};

		donationForm.querySelectorAll('[data-field-visibility-conditions]').forEach(function (inputField) {
			const visibilityConditions = JSON.parse(inputField.getAttribute('data-field-visibility-conditions'));
			const visibilityCondition = visibilityConditions[0]; // Currently we support only one visibility condition.
			let fieldSelector = getFieldSelector(inputField);
			let {field} = visibilityCondition;

			// Get field. It will tell use real name of field.
			field = document.querySelector(`[name="${field}"], [name="${field}[]"]`);

			if (field) {
				fields[field.name] = {
					...fields[field],
					[fieldSelector]: visibilityConditions
				}
			}
		});

		return fields;
	}

	/**
	 * @since 2.15.0
	 *
	 * @param operator
	 * @param firstData
	 * @param secondData
	 *
	 * @return boolean
	 */
	function compareWithOperator( operator, firstData, secondData ){
		return {
			'=': firstData === secondData,
			'!=': firstData != secondData,
			'>': firstData > secondData,
			'>=': firstData >= secondData,
			'<': firstData < secondData,
			'<=': firstData <=secondData
		}[operator]
	}

	/**
	 * Handle fields visibility.
	 * @since 2.15.0
	 */
	function handleVisibility(donationForm, watchedFieldName, visibilityConditionsForWatchedField) {
		for (const [inputFieldName, visibilityConditions] of Object.entries(visibilityConditionsForWatchedField)) {
			const inputField = -1 === inputFieldName.indexOf('data-field-name') ?
				donationForm.querySelector(`[name="${inputFieldName}"]`) :
				donationForm.querySelector(inputFieldName);
			const fieldWrapperWithoutInputField = inputField.classList.contains('.form-row');
			const fieldWrapper = fieldWrapperWithoutInputField ? inputField : inputField.closest('.form-row');
			const visibilityCondition = visibilityConditions[0]; // Currently we support only one visibility condition.
			let visible = false;
			const {operator, value} = visibilityCondition;

			const inputs = donationForm.querySelectorAll(`[name="${watchedFieldName}"]`);
			let hasFieldController = !!inputs.length;

			if (hasFieldController) {
				inputs.forEach((input) => {
					const fieldType = input.getAttribute('type');
					const comparisonResult = compareWithOperator(operator, input.value, value);

					if (fieldType && (fieldType === 'radio' || fieldType === 'checkbox')) {
						if (input.checked && comparisonResult) {
							visible = true;
						}
					} else if (comparisonResult) {
						visible = true;
					}
				});

				// Show or Hide field wrapper.
				visible ?
					fieldWrapper.classList.remove('give-hidden') :
					fieldWrapper.classList.add('give-hidden');
			}
		}
	}

	/**
	 * Setup state for condition visibility settings.
	 * state contains list of watched elements per donation form.
	 *
	 * @since 2.15.0
	 */
	function addVisibilityConditionsToStateForDonationForm(donationForm) {
		const uniqueDonationFormId = donationForm.getAttribute('data-id');
		const watchedFields = getWatchedElementNames(donationForm);

		// Add donation form to state only if visibility conditions exiting for at least form field.
		if (uniqueDonationFormId && Object.keys(watchedFields).length) {
			state[uniqueDonationFormId] = watchedFields;
		}
	}

	/**
	 * @since 2.15.0
	 * @param donationForm
	 */
	function applyVisibilityConditionsToDonationForm(donationForm) {
		const uniqueDonationFormId = donationForm.getAttribute('data-id');

		if (uniqueDonationFormId && (uniqueDonationFormId in state)) {
			const formState = state[uniqueDonationFormId];

			for (const [watchedFieldName, visibilityConditions] of Object.entries(formState)) {
				handleVisibility(
					document.querySelector(`form[data-id="${uniqueDonationFormId}"]`)
						.closest('.give-form'),
					watchedFieldName,
					visibilityConditions
				);
			}
		}
	}

	/**
	 * @since 2.15.0
	 */
	function addChangeEventToWatchedElementsForDonationForm(donationFormUniqueId) {
		const donationForm = document
			.querySelector(`form.give-form[data-id="${donationFormUniqueId}"`)
			.closest('form.give-form');

		if (!donationForm || !state.hasOwnProperty(donationFormUniqueId)) {
			return;
		}

		for (const [watchedElementName, VisibilityConditions] of Object.entries(state[donationFormUniqueId])) {
			document.querySelectorAll(`[name = "${watchedElementName}"]`)
				.forEach(field => {
					field.addEventListener(
						'change',
						() => handleVisibility(donationForm, watchedElementName, VisibilityConditions)
					);
				});
		}
	}

	/**
	 * @since 2.15.0
	 */
	function bootVisibilityConditionsFormAllDonationForm() {
		document.querySelectorAll('form.give-form').forEach(addVisibilityConditionsToStateForDonationForm);

		// Apply visibility conditions.
		// Add change event to watched field.
		for (const [donationFormUniqueId, donationFormState] of Object.entries(state)) {
			for (const [watchedFieldName, visibilityConditions] of Object.entries(donationFormState)) {
				handleVisibility(
					document.querySelector(`form[data-id="${donationFormUniqueId}"]`)
						.closest('.give-form'),
					watchedFieldName,
					visibilityConditions
				);
			}

			addChangeEventToWatchedElementsForDonationForm(donationFormUniqueId);
		}
	}

	bootVisibilityConditionsFormAllDonationForm();

	// Apply visibility conditions to donation form when donor switch gateway.
	document.addEventListener(
		'give_gateway_loaded',
		event => {
			const donationForm = document.getElementById(event.detail.formIdAttribute);
			const uniqueDonationFormId = donationForm.getAttribute('data-id');
			addVisibilityConditionsToStateForDonationForm(donationForm);
			applyVisibilityConditionsToDonationForm(donationForm);
			addChangeEventToWatchedElementsForDonationForm(uniqueDonationFormId)
		}
	);
});

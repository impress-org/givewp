window.addEventListener('load', async () => {
	const state = {};

	/**
	 * Get list of watched fields.
	 * @unreleased
	 *
	 * @return object
	 */
	function getWatchedElementNames(donationForm) {
		const fields = {};

		donationForm.querySelectorAll('[data-field-visibility-conditions]').forEach(function (inputField) {
			const visibilityConditions = JSON.parse(inputField.getAttribute('data-field-visibility-conditions'));
			const visibilityCondition = visibilityConditions[0]; // Currently we support only one visibility condition.
			const {field} = visibilityCondition;
			const fieldSelector = inputField.name ?
				inputField.name :
				`[data-field-name="${inputField.closest('.form-row').getAttribute('data-field-name')}"] ${inputField.nodeName.toLowerCase()}`

			fields[field] = {
				...fields[field],
				[fieldSelector]: visibilityConditions
			}
		})

		return fields;
	}

	/**
	 * @unreleased
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
	 * @unreleased
	 */
	function handleVisibility(donationForm, visibilityConditionsForWatchedField) {
		for (const [inputFieldName, visibilityConditions] of Object.entries(visibilityConditionsForWatchedField)) {
			const inputField = -1 === inputFieldName.indexOf('data-field-name') ?
				donationForm.querySelector(`[name="${inputFieldName}"]`) :
				donationForm.querySelector(inputFieldName);
			const fieldWrapper = inputField.closest('.form-row');
			const visibilityCondition = visibilityConditions[0]; // Currently we support only one visibility condition.
			let visible = false;
			const {field, operator, value} = visibilityCondition;

			const inputs = donationForm.querySelectorAll(`[name="${field}"], [name="${field}[]"]`);
			let hasFieldController = !!inputs.length;

			// Do not apply visibility conditions if field controller does not exit in DOM.
			if (!hasFieldController) {
				return;
			}

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

	/**
	 * Setup state for condition visibility settings.
	 * state contains list of watched elements per donation form.
	 *
	 * @unreleased
	 */
	function addVisibilityConditionsToStateForDonationForm(donationForm) {
		const uniqueDonationFormId = donationForm.getAttribute('data-id');
		const watchedFields = getWatchedElementNames(donationForm);

		// Add donation form to state only if visibility conditions exiting for at least form field.
		if (!uniqueDonationFormId || !Object.entries(watchedFields).length) {
			return false;
		}

		state[uniqueDonationFormId] = {
			watchedElements: watchedFields,
			...state[uniqueDonationFormId]
		}
	}

	/**
	 * Setup state for condition visibility settings.
	 * state contains list of watched elements per donation form.
	 *
	 * @unreleased
	 * @returns {Promise<void>}
	 */
	function addVisibilityConditionsToStateForAllDonationForm() {
		document.querySelectorAll('form.give-form')
			.forEach(function (donationForm) {
				const uniqueDonationFormId = donationForm.getAttribute('data-id');
				const watchedFields = getWatchedElementNames(donationForm);

				// Add donation form to state only if visibility conditions exiting for at least form field.
				if (!uniqueDonationFormId || !Object.entries(watchedFields).length) {
					return false;
				}

				state[uniqueDonationFormId] = {
					watchedElements: watchedFields,
					...state[uniqueDonationFormId]
				}
			});
	}

	/**
	 * @unreleased
	 */
	function applyVisibilityConditionsAttachedToWatchedField(donationForm, fieldName) {
		const uniqueDonationFormId = donationForm.getAttribute('data-id');

		// Exit if field is not element of donation form.
		if (
			!donationForm ||
			!uniqueDonationFormId ||
			!state.hasOwnProperty(uniqueDonationFormId)
		) {
			return false;
		}

		const formState = state[uniqueDonationFormId];
		const watchedElementNames = Object.keys(formState.watchedElements);

		// Exit if field is not in list of watched elements.
		if (!watchedElementNames.includes(fieldName)) {
			return false;
		}

		handleVisibility(donationForm, formState.watchedElements[fieldName])
	}

	/**
	 * @unreleased
	 * @param donationForm
	 */
	function applyVisibilityConditionsToDonationForm(donationForm) {
		const uniqueDonationFormId = donationForm.getAttribute('data-id');

		// Exit if field is not element of donation form.
		if (
			!uniqueDonationFormId ||
			!state.hasOwnProperty(uniqueDonationFormId)
		) {
			return false;
		}

		const formState = state[uniqueDonationFormId];

		for (const [watchedFieldName, visibilityConditions] of Object.entries(formState.watchedElements)) {
			handleVisibility(
				document.querySelector(`form[data-id="${uniqueDonationFormId}"]`)
					.closest('.give-form'),
				visibilityConditions
			);
		}
	}

	/**
	 * @unreleased
	 */
	function applyVisibilityConditionsToAllDonationForm() {
		for (const [uniqueDonationFormId, donationFormState] of Object.entries(state)) {
			for (const [watchedFieldName, visibilityConditions] of Object.entries(donationFormState.watchedElements)) {
				handleVisibility(
					document.querySelector(`form[data-id="${uniqueDonationFormId}"]`)
						.closest('.give-form'),
					visibilityConditions
				);
			}
		}
	}

	await addVisibilityConditionsToStateForAllDonationForm();
	console.log(state);
	applyVisibilityConditionsToAllDonationForm();

	// Apply visibility conditions to donation form when donor switch gateway.
	document.addEventListener(
		'give_gateway_loaded',
		event => {
			const donationForm = document.getElementById(event.detail.formIdAttribute);
			addVisibilityConditionsToStateForDonationForm(donationForm);
			applyVisibilityConditionsToDonationForm(donationForm);
		}
	);


	// Look for change in watched elements.
	document.addEventListener(
		'change',
		event => applyVisibilityConditionsAttachedToWatchedField(
			event.target.closest('form.give-form'),
			event.target.getAttribute('name').replace('[]', '')
		)
	);
});

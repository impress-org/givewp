document.addEventListener('readystatechange', (event) => {
    if (event.target.readyState !== 'complete') {
        return null;
    }

    const state = {};

    /**
     * Get list of watched fields.
     * @since 2.15.0
     *
     * @return object
     */
    function getWatchedElementNames(donationForm) {
        const fields = {};

        donationForm.querySelectorAll('[data-field-visibility-conditions]').forEach(function (fieldContainer) {
            const visibilityConditions = JSON.parse(fieldContainer.getAttribute('data-field-visibility-conditions'));
            const visibilityCondition = visibilityConditions[0]; // Currently we support only one visibility condition.
            let fieldContainerSelector = `[data-field-name="${fieldContainer.getAttribute('data-field-name')}"]`;
            let {field} = visibilityCondition;

            // Get field. It will tell use real name of field.
            field = document.querySelector(`[name="${field}"], [name="${field}[]"]`);

            if (field) {
                fields[field.name] = {
                    ...fields[field.name],
                    [fieldContainerSelector]: visibilityConditions,
                };
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
    function compareWithOperator(operator, firstData, secondData) {
        return {
            '=': firstData === secondData,
            '!=': firstData !== secondData,
            '>': firstData > secondData,
            '>=': firstData >= secondData,
            '<': firstData < secondData,
            '<=': firstData <= secondData,
        }[operator];
    }

    /**
     * Handle fields visibility.
     * @since 2.15.0
     */
    function handleVisibility(donationForm, watchedFieldName, visibilityConditionsForWatchedField) {
        for (const [fieldContainerSelector, visibilityConditions] of Object.entries(
            visibilityConditionsForWatchedField
        )) {
            const fieldWrapper = donationForm.querySelector(fieldContainerSelector);
            const fieldName = fieldWrapper.getAttribute('data-field-name');
            const visibilityCondition = visibilityConditions[0]; // Currently we support only one visibility condition.
            let visible = false;
            const {comparisonOperator} = visibilityCondition;
            let {value} = visibilityCondition;

            const inputs = donationForm.querySelectorAll(`[name="${watchedFieldName}"]`);
            let hasFieldController = !!inputs.length;

            if (hasFieldController) {
                inputs.forEach((input) => {
                    const fieldType = input.getAttribute('type');
                    let inputValue = input.value;

                    // Make an exception for the amount field and parse the value
                    if (input.name === 'give-amount') {
                        inputValue = Give.fn.unFormatCurrency(
                            input.value,
                            Give.form.fn.getInfo('decimal_separator', donationForm)
                        );

                        value = Math.abs(parseFloat(value));
                    }

                    const comparisonResult = compareWithOperator(comparisonOperator, inputValue, value);

                    if (fieldType === 'checkbox') {
                        if (
                            (comparisonResult && input.checked && comparisonOperator === '=') ||
                            (!input.checked && comparisonOperator === '!=')
                        ) {
                            visible = true;
                        }
                    } else if (fieldType === 'radio') {
                        if (input.checked && comparisonResult) {
                            visible = true;
                        }
                    } else if (comparisonResult) {
                        visible = true;
                    }
                });

                // Show or Hide field wrapper.
                if (visible) {
                    const field = fieldWrapper.querySelector(`[name="${fieldName}"][data-required]`);
                    fieldWrapper.classList.remove('give-hidden');

                    // Make hidden flagged required field required.
                    if (field) {
                        field.setAttribute('required', '');
                        field.removeAttribute('data-required');
                    }
                } else {
                    const field = fieldWrapper.querySelector(`[name="${fieldName}"][required]`);
                    fieldWrapper.classList.add('give-hidden');

                    // Make hidden required field non-required.
                    if (field) {
                        field.removeAttribute('required');
                        field.setAttribute('data-required', '1');
                    }
                }
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

        if (uniqueDonationFormId && uniqueDonationFormId in state) {
            const formState = state[uniqueDonationFormId];

            for (const [watchedFieldName, visibilityConditions] of Object.entries(formState)) {
                handleVisibility(
                    document.querySelector(`form[data-id="${uniqueDonationFormId}"]`).closest('.give-form'),
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
            document.querySelectorAll(`[name = "${watchedElementName}"]`).forEach((field) => {
                jQuery(field).on(
                    'input change blur',
                    handleVisibility.bind(null, donationForm, watchedElementName, VisibilityConditions)
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
                    document.querySelector(`form[data-id="${donationFormUniqueId}"]`).closest('.give-form'),
                    watchedFieldName,
                    visibilityConditions
                );
            }

            addChangeEventToWatchedElementsForDonationForm(donationFormUniqueId);
        }
    }

    bootVisibilityConditionsFormAllDonationForm();

    // Apply visibility conditions to donation form when donor switch gateway.
    document.addEventListener('give_gateway_loaded', (event) => {
        const donationForm = document.getElementById(event.detail.formIdAttribute);
        const uniqueDonationFormId = donationForm.getAttribute('data-id');
        addVisibilityConditionsToStateForDonationForm(donationForm);
        applyVisibilityConditionsToDonationForm(donationForm);
        addChangeEventToWatchedElementsForDonationForm(uniqueDonationFormId);
    });
});

/**
 * External Dependencies
 */
import { getDonorOptionsWindowData } from '@givewp/donors/utils';
import { __ } from '@wordpress/i18n';

const donorOptionsData = getDonorOptionsWindowData();
const { states } = donorOptionsData;

/**
 * @since 4.4.0
 */
export interface StateOption {
    value: string;
    label: string;
}

/**
 * Get states configuration for a given country
 *
 * @since 4.4.0
 */
export interface StatesConfig {
    hasStates: boolean;
    states: StateOption[];
    stateLabel: string;
    isRequired: boolean;
    showField: boolean;
}

/**
 * @since 4.4.0
 */
export const getStatesForCountry = (countryCode: string): StatesConfig => {
    if (!countryCode) {
        return {
            hasStates: false,
            states: [],
            stateLabel: __('State', 'give'),
            isRequired: false,
            showField: false,
        };
    }

    // Safety checks for states data
    const noStatesCountries = states?.noStatesCountries || [];
    const statesNotRequiredCountries = states?.statesNotRequiredCountries || [];
    const stateLabels = states?.labels || {};
    const statesList = states?.list || {};

    const showField = !noStatesCountries.includes(countryCode);
    const isRequired = showField && !statesNotRequiredCountries.includes(countryCode);
    const stateLabel = stateLabels[countryCode] || __('State', 'give');
    const countryStates = statesList[countryCode] || {};

    const stateOptions: StateOption[] = Object.entries(countryStates).map(([value, label]) => ({
        value,
        label: String(label),
    }));

    const nonEmptyStates = stateOptions.filter(state => state.value && state.value.trim() !== '');

    return {
        hasStates: nonEmptyStates.length > 0,
        states: nonEmptyStates,
        stateLabel,
        isRequired,
        showField,
    };
};

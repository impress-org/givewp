/**
 * External Dependencies
 */
import { getDonorOptionsWindowData } from '@givewp/donors/utils';
import { __ } from '@wordpress/i18n';

const { states } = getDonorOptionsWindowData();

export interface StateOption {
    value: string;
    label: string;
}

/**
 * Get states configuration for a given country
 *
 * @unreleased
 */
export const getStatesForCountry = (countryCode: string): {
    hasStates: boolean;
    states: StateOption[];
    stateLabel: string;
    isRequired: boolean;
    showField: boolean;
} => {
    if (!countryCode) {
        return {
            hasStates: false,
            states: [],
            stateLabel: __('State', 'give'),
            isRequired: false,
            showField: false,
        };
    }

    const showField = !states.noStatesCountries.includes(countryCode);
    const isRequired = showField && !states.statesNotRequiredCountries.includes(countryCode);
    const stateLabel = states.labels[countryCode] || __('State', 'give');
    const countryStates = states.list[countryCode] || {};

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

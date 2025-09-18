import { Donor } from '@givewp/donors/admin/components/types';
import { DonorOption } from './types';

/**
 * Formats a donor object into a select option
 *
 * @since 4.8.0
 */
export function formatDonorOption(donor: Donor): DonorOption {
    return {
        value: donor.id,
        label: `${donor.name} (${donor.email})`,
    };
}

/**
 * Formats multiple donors into select options
 *
 * @since 4.8.0
 */
export function formatDonorOptions(donors: Donor[]): DonorOption[] {
    return (donors || []).map(formatDonorOption);
}

/**
 * Deduplicates and sorts donor options
 *
 * @since 4.8.0
 */
export function processOptionsForMenu(
    options: DonorOption[],
    selectedOption: DonorOption | null = null
): DonorOption[] {
    // Remove duplicates and sort alphabetically
    const filteredOptions = options
        .filter((option, index, self) =>
            index === self.findIndex((t) => t.value === option.value)
        )
        .sort((a, b) => a.label.localeCompare(b.label));

    // If no selected option, return filtered list
    if (!selectedOption) {
        return filteredOptions;
    }

    // Put selected option first, then other options (excluding the selected one)
    return [
        selectedOption,
        ...filteredOptions.filter(option => option.value !== selectedOption.value)
    ];
}

/**
 * Creates query parameters for donor API requests
 *
 * @since 4.8.0
 */
export function createDonorQueryParams(config: {
    mode: 'live' | 'test';
    perPage: number;
    page: number;
    searchInput?: string;
}): URLSearchParams {
    const { mode, perPage, page, searchInput } = config;

    return new URLSearchParams({
        mode,
        per_page: perPage.toString(),
        page: page.toString(),
        sort: 'name',
        direction: 'ASC',
        includeSensitiveData: 'true',
        anonymousDonors: 'include',
        onlyWithDonations: 'false',
        ...(searchInput && { search: searchInput }),
    });
}

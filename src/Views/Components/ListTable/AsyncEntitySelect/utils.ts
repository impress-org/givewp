/**
 * @since 4.10.0
 */
export interface CampaignOption {
    value: number;
    label: string;
}

/**
 * Deduplicates and sorts form options
 *
 * @since 4.10.0
 */
export function processOptionsForMenu(
    options: CampaignOption[],
    selectedOption: CampaignOption | null = null
): CampaignOption[] {
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

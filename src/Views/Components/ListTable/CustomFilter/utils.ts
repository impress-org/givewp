/**
 * @unreleased
 */
export type Campaign = {
    id: number;
    title: string;
}

/**
 * @unreleased
 */
export interface CampaignOption {
    value: number;
    label: string;
}

/**
 * Creates query parameters for form API requests
 *
 * @unreleased
 */
export function createCampaignQueryParams(config: {
    perPage: number;
    page: number;
    search?: string;
}): URLSearchParams {
    const { perPage, page, search } = config;

    return new URLSearchParams({
        per_page: perPage.toString(),
        page: page.toString(),
        ...(search && { search: search }),
    });
}

/**
 * Deduplicates and sorts form options
 *
 * @unreleased
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

/**
 * Formats a campaign object into a select option
 *
 * @unreleased
 */ 
export function formatCampaignOption(campaign: Campaign): CampaignOption {
    return {
        value: campaign.id,
        label: campaign.title,
    };
}

/**
 * Formats multiple campaigns into select options
 *
 * @unreleased
 */
export function formatCampaignOptions(campaigns: Campaign[]): CampaignOption[] {
    return (campaigns || []).map(formatCampaignOption);
}
/**
 * @unreleased
 */
export interface DonorOption {
    value: number;
    label: string;
}

/**
 * @unreleased
 */
export interface UseDonorAsyncSelectReturn {
    selectedOption: DonorOption | null;
    loadOptions: (searchInput: string) => Promise<{
        options: DonorOption[];
        hasMore: boolean;
    }>;
    mapOptionsForMenu: (options: DonorOption[]) => DonorOption[];
    error: Error | null;
}


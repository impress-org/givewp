/**
 * @since 4.8.0
 */
export interface DonorOption {
    value: number;
    label: string;
}

/**
 * @since 4.8.0
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


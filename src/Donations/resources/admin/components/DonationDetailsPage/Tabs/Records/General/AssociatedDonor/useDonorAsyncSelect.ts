import { useCallback, useEffect, useState } from 'react';
import { useEntityRecord } from '@wordpress/core-data';
import apiFetch from '@wordpress/api-fetch';
import { Donor } from '@givewp/donors/admin/components/types';
import {
    DonorOption,
    UseDonorAsyncSelectReturn
} from './types';
import {
    formatDonorOption,
    formatDonorOptions,
    processOptionsForMenu,
    createDonorQueryParams
} from './utils';
import { getDonationOptionsWindowData } from '@givewp/donations/utils';

const DONORS_PER_PAGE = 30;

/**
 * Custom hook for handling async donor selection with pagination and search
 *
 * @since 4.8.0
 */
export function useDonorAsyncSelect(selectedDonorId: number | null): UseDonorAsyncSelectReturn {
    const { mode } = getDonationOptionsWindowData();
    const [page, setPage] = useState(0);
    const [selectedOption, setSelectedOption] = useState<DonorOption | null>(null);
    const [error, setError] = useState<Error | null>(null);

    // Load current donor details if we have a selected donor ID
    const {
        record: currentDonor,
        hasResolved: hasResolvedDonor,
    } = useEntityRecord<Donor>('givewp', 'donor', selectedDonorId);

    // Update selected option when current donor changes
    useEffect(() => {
        if (hasResolvedDonor && currentDonor && selectedDonorId) {
            setSelectedOption(formatDonorOption(currentDonor));
        } else if (!selectedDonorId) {
            setSelectedOption(null);
        }
    }, [currentDonor, hasResolvedDonor, selectedDonorId]);

    // Load options function for AsyncPaginate
    const loadOptions = useCallback(async (searchInput: string) => {
        const currentPage = searchInput ? 1 : page + 1;

        setError(null);

        try {
            const queryParams = createDonorQueryParams({
                mode,
                perPage: DONORS_PER_PAGE,
                page: currentPage,
                searchInput: searchInput || undefined,
            });

            const donors = await apiFetch<Donor[]>({
                path: `/givewp/v3/donors?${queryParams.toString()}`,
            });

            const newOptions = formatDonorOptions(donors);

            // Update page state
            if (searchInput !== '') {
                setPage(1);
            } else if (!searchInput) {
                setPage(currentPage);
            }

            const hasMoreResults = (donors?.length || 0) >= DONORS_PER_PAGE;

            return {
                options: newOptions,
                hasMore: hasMoreResults,
            };
        } catch (err) {
            const loadError = err instanceof Error ? err : new Error('Failed to load donors');
            setError(loadError);
            console.error('Error loading donors:', loadError);

            return {
                options: [],
                hasMore: false,
            };
        }
    }, [mode, page]);

    // Map options for menu (deduplication and ordering)
    const mapOptionsForMenu = useCallback(
        (options: DonorOption[]) => processOptionsForMenu(options, selectedOption),
        [selectedOption]
    );

    return {
        selectedOption,
        loadOptions,
        mapOptionsForMenu,
        error,
    };
}

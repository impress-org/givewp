import { useCallback, useEffect, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { createCampaignQueryParams, processOptionsForMenu, formatCampaignOptions } from './utils';
import { Campaign, CampaignOption } from './utils';

const CAMPAIGNS_PER_PAGE = 30;

/**
 * @unreleased
 */
type UseCampaignAsyncSelectReturn = {
    selectedOption: CampaignOption | null;
    setSelectedOption: (option: CampaignOption | null) => void;
    loadOptions: (searchInput: string) => Promise<{
        options: CampaignOption[];
        hasMore: boolean;
    }>;
    mapOptionsForMenu: (options: CampaignOption[]) => CampaignOption[];
    error: Error | null;
}

/**
 * Custom hook for handling async form selection with pagination and search
 *
 * @unreleased
 */
export function useCampaignAsyncSelect(): UseCampaignAsyncSelectReturn {
    const [page, setPage] = useState(0);
    const [selectedOption, setSelectedOption] = useState<CampaignOption | null>(null);
    const [error, setError] = useState<Error | null>(null);

    // Load options function for AsyncPaginate
    const loadOptions = useCallback(async (search: string) => {
        const currentPage = search ? 1 : page + 1;

        setError(null);

        try {
            const queryParams = createCampaignQueryParams({
                perPage: CAMPAIGNS_PER_PAGE,
                page: currentPage,
                search: search || undefined,
            });

            const campaigns = await apiFetch<Campaign[]>({
                path: `/givewp/v3/campaigns?${queryParams.toString()}`,
            });

            const newOptions = formatCampaignOptions(campaigns);

            // Update page state
            if (search !== '') {
                setPage(1);
            } else if (!search) {
                setPage(currentPage);
            }

            const hasMoreResults = (campaigns?.length || 0) >= CAMPAIGNS_PER_PAGE;

            return {
                options: newOptions,
                hasMore: hasMoreResults,
            };
        } catch (err) {
            const loadError = err instanceof Error ? err : new Error('Failed to load campaigns');
            setError(loadError);
            console.error('Error loading campaigns:', loadError);

            return {
                options: [],
                hasMore: false,
            };
        }
    }, [page]);

    // Map options for menu (deduplication and ordering)
    const mapOptionsForMenu = useCallback(
        (options: CampaignOption[]) => processOptionsForMenu(options, selectedOption),
        [selectedOption]
    );

    return {
        selectedOption,
        setSelectedOption,
        loadOptions,
        mapOptionsForMenu,
        error,
    };
}

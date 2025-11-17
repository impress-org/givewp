import { useCallback, useEffect, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { createCampaignQueryParams, processOptionsForMenu, formatCampaignOptions, formatCampaignOption } from './utils';
import { Campaign, CampaignOption } from './utils';
import { useEntityRecord } from '@wordpress/core-data';

const CAMPAIGNS_PER_PAGE = 30;

/**
 * @since 4.10.0
 */
type UseCampaignAsyncSelectReturn = {
    selectedOption: CampaignOption | null;
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
 * @since 4.10.0
 */
export function useCampaignAsyncSelect(selectedCampaignId: number | null): UseCampaignAsyncSelectReturn {
    const [page, setPage] = useState(0);
    const [selectedOption, setSelectedOption] = useState<CampaignOption | null>(null);
    const [error, setError] = useState<Error | null>(null);

    // Load current campaign details if we have a selected campaign ID
    const {
        record: currentCampaign,
        hasResolved: hasResolvedCampaign,
    } = useEntityRecord<Campaign>('givewp', 'campaign', selectedCampaignId);

    // Update selected option when current campaign changes
    useEffect(() => {
        if (hasResolvedCampaign && currentCampaign && selectedCampaignId) {
            setSelectedOption(formatCampaignOption(currentCampaign));
        } else if (!selectedCampaignId) {
            setSelectedOption(null);
        }
    }, [currentCampaign, hasResolvedCampaign, selectedCampaignId]);

    // Load options function for AsyncPaginate
    const loadOptions = useCallback(async (search: string) => {
        const currentPage = search ? 1 : page + 1;

        setError(null);

        try {
            const queryParams = createCampaignQueryParams({
                perPage: CAMPAIGNS_PER_PAGE,
                page: currentPage,
                search: search || undefined,
                status: ['active', 'draft', 'archived'],
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
        loadOptions,
        mapOptionsForMenu,
        error,
    };
}

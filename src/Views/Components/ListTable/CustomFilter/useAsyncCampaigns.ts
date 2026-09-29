import { useCallback, useEffect, useState } from 'react';
import apiFetch from '@wordpress/api-fetch';
import { createCampaignQueryParams, processOptionsForMenu, formatCampaignOptions, formatCampaignOption } from './utils';
import { CampaignOption } from './utils';
import { useEntityRecord } from '@wordpress/core-data';

const CAMPAIGNS_PER_PAGE = 30;

/**
 * The REST collection an async select loads its options from. Both campaigns and forms return `id` and `title`.
 *
 * @since TBD
 */
export type AsyncSelectEntity = {
    name: 'campaign' | 'form';
    path: string;
    status: string[];
};

/**
 * @since TBD
 */
export const asyncSelectEntities: Record<AsyncSelectEntity['name'], AsyncSelectEntity> = {
    campaign: {name: 'campaign', path: '/givewp/v3/campaigns', status: ['active', 'draft', 'archived']},
    form: {name: 'form', path: '/givewp/v3/forms', status: ['publish', 'private', 'draft', 'upgraded']},
};

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
 * Custom hook for handling async campaign or form selection with pagination and search
 *
 * @since TBD Accept the entity to load, defaulting to campaigns.
 * @since 4.10.0
 */
export function useCampaignAsyncSelect(
    selectedCampaignId: number | null,
    entity: AsyncSelectEntity = asyncSelectEntities.campaign
): UseCampaignAsyncSelectReturn {
    const [page, setPage] = useState(0);
    const [selectedOption, setSelectedOption] = useState<CampaignOption | null>(null);
    const [error, setError] = useState<Error | null>(null);

    // Load the selected campaign or form so its title shows in the select
    const {
        record: currentRecord,
        hasResolved: hasResolvedRecord,
    } = useEntityRecord<{id: number; title: string}>('givewp', entity.name, selectedCampaignId);

    // Update selected option when the selected record changes
    useEffect(() => {
        if (hasResolvedRecord && currentRecord && selectedCampaignId) {
            setSelectedOption(formatCampaignOption(currentRecord));
        } else if (!selectedCampaignId) {
            setSelectedOption(null);
        }
    }, [currentRecord, hasResolvedRecord, selectedCampaignId]);

    // Load options function for AsyncPaginate
    const loadOptions = useCallback(async (search: string) => {
        const currentPage = search ? 1 : page + 1;

        setError(null);

        try {
            const queryParams = createCampaignQueryParams({
                perPage: CAMPAIGNS_PER_PAGE,
                page: currentPage,
                search: search || undefined,
                status: entity.status,
            });

            const records = await apiFetch<{id: number; title: string}[]>({
                path: `${entity.path}?${queryParams.toString()}`,
            });

            const newOptions = formatCampaignOptions(records);

            // Update page state
            if (search !== '') {
                setPage(1);
            } else if (!search) {
                setPage(currentPage);
            }

            const hasMoreResults = (records?.length || 0) >= CAMPAIGNS_PER_PAGE;

            return {
                options: newOptions,
                hasMore: hasMoreResults,
            };
        } catch (err) {
            const loadError = err instanceof Error ? err : new Error(`Failed to load ${entity.name}s`);
            setError(loadError);
            console.error(`Error loading ${entity.name}s:`, loadError);

            return {
                options: [],
                hasMore: false,
            };
        }
    }, [page, entity]);

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

import { useCallback, useMemo, useState } from 'react';
import { resolveSelect, useSelect } from '@wordpress/data';
import { store as coreStore } from '@wordpress/core-data';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';
import { processOptionsForMenu } from './utils';
import { CampaignOption } from './utils';

const CAMPAIGNS_PER_PAGE = 30;

/**
 * The core-data entity an async select loads its options from.
 *
 * @since TBD
 */
export type AsyncSelectEntity = {
    kind: string;
    name: string;
    listQuery: Record<string, string>;
    editorListQuery?: Record<string, string>;
    recordQuery?: Record<string, string>;
    getTitle: (record: any) => string;
};

/* The view context keeps forms readable for users who cannot edit them. */
const formRecordQuery = {context: 'view', _fields: 'id,title,status'};

/**
 * Forms load from the post type entity because the givewp form entity leaves out v2 forms.
 *
 * Draft forms are listed as well, since a form taken offline keeps its donations. The route only accepts a
 * request for drafts from users who can edit forms, so `editorListQuery` applies to them alone.
 *
 * @since TBD
 */
export const asyncSelectEntities: Record<'campaign' | 'form', AsyncSelectEntity> = {
    campaign: {
        kind: 'givewp',
        name: 'campaign',
        listQuery: {status: 'active,draft,archived'},
        getTitle: (campaign) => campaign.title,
    },
    form: {
        kind: 'postType',
        name: 'give_forms',
        listQuery: {
            ...formRecordQuery,
            status: 'publish',
            orderby: 'title',
            order: 'asc',
            search_columns: 'post_title',
        },
        editorListQuery: {status: 'publish,draft'},
        recordQuery: formRecordQuery,
        getTitle: (form) => {
            const title = decodeEntities(form.title.rendered);

            /* translators: %s: donation form title */
            return form.status === 'draft' ? sprintf(__('%s (Draft)', 'give'), title) : title;
        },
    },
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
 * @since TBD Accept the entity to load, defaulting to campaigns, and load options through core-data.
 * @since TBD Work out the page from the options already loaded, so a search can load past its first page.
 * @since 4.10.0
 */
export function useCampaignAsyncSelect(
    selectedCampaignId: number | null,
    entity: AsyncSelectEntity = asyncSelectEntities.campaign
): UseCampaignAsyncSelectReturn {
    const [error, setError] = useState<Error | null>(null);

    // Load the selected campaign or form so its title shows in the select
    const selectedTitle = useSelect(
        (select) => {
            if (!selectedCampaignId) {
                return null;
            }

            // @ts-ignore
            const record = select(coreStore).getEntityRecord(entity.kind, entity.name, selectedCampaignId, entity.recordQuery);

            return record ? entity.getTitle(record) : null;
        },
        [entity, selectedCampaignId]
    );

    const selectedOption = useMemo<CampaignOption | null>(
        () => (selectedCampaignId && selectedTitle !== null ? {value: selectedCampaignId, label: selectedTitle} : null),
        [selectedCampaignId, selectedTitle]
    );

    // Load options function for AsyncPaginate
    const loadOptions = useCallback(async (search: string, loadedOptions: CampaignOption[] = []) => {
        /* The select passes the options it holds for this search, and holds none after a new search. */
        const currentPage = Math.floor(loadedOptions.length / CAMPAIGNS_PER_PAGE) + 1;

        setError(null);

        try {
            const canEdit = entity.editorListQuery
                // @ts-ignore
                ? await resolveSelect(coreStore).canUser('create', entity.name).catch(() => false)
                : false;

            // @ts-ignore
            const records: any[] = (await resolveSelect(coreStore).getEntityRecords(entity.kind, entity.name, {
                ...entity.listQuery,
                ...(canEdit && entity.editorListQuery),
                per_page: CAMPAIGNS_PER_PAGE,
                page: currentPage,
                ...(search && {search}),
            })) ?? [];

            const newOptions = records.map((record) => ({value: record.id, label: entity.getTitle(record)}));

            const hasMoreResults = records.length >= CAMPAIGNS_PER_PAGE;

            return {
                options: newOptions,
                hasMore: hasMoreResults,
            };
        } catch (err) {
            /* The post type route answers a page past the last one with an error instead of an empty list. */
            if (err?.code === 'rest_post_invalid_page_number') {
                return {
                    options: [],
                    hasMore: false,
                };
            }

            const loadError = err instanceof Error ? err : new Error(`Failed to load ${entity.name} options`);
            setError(loadError);
            console.error(`Error loading ${entity.name} options:`, loadError);

            return {
                options: [],
                hasMore: false,
            };
        }
    }, [entity]);

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

import {resolveSelect} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';
import type {Option} from '@wordpress/dataviews';
import {asyncSelectEntities} from '../AsyncEntitySelect/useAsyncCampaigns';

/**
 * The REST API's largest page.
 */
const OPTIONS_PER_PAGE = 100;

/**
 * A guard against a runaway loop; 50 pages is 5 000 campaigns or forms.
 */
const MAX_PAGES = 50;

/**
 * Loads every option of a campaign or form filter, page by page, through the same core-data
 * entities the old filter selects used. The DataViews filter searches its options in the browser, so
 * it needs all of them, not the first page. It runs when the admin opens the filter, never per row.
 * Users who can edit forms also see draft forms.
 *
 * @since TBD
 */
export async function loadEntityElements(entityName: 'campaign' | 'form'): Promise<Option[]> {
    const entity = asyncSelectEntities[entityName];

    const store = resolveSelect(coreStore);
    const canEdit = entity.editorListQuery ? await store.canUser('create', entity.name).catch(() => false) : false;

    const options: Option[] = [];

    for (let page = 1; page <= MAX_PAGES; page++) {
        let records: Array<{id: number}>;

        try {
            records =
                (await store.getEntityRecords(entity.kind, entity.name, {
                    ...entity.listQuery,
                    ...(canEdit && entity.editorListQuery),
                    per_page: OPTIONS_PER_PAGE,
                    page,
                })) ?? [];
        } catch (error) {
            // The post type route answers a page past the last one with an error instead of an empty list.
            if (error?.code === 'rest_post_invalid_page_number') {
                break;
            }

            throw error;
        }

        options.push(...records.map((record) => ({value: record.id, label: entity.getTitle(record)})));

        if (records.length < OPTIONS_PER_PAGE) {
            break;
        }
    }

    return options;
}

import {resolveSelect} from '@wordpress/data';
import {store as coreStore} from '@wordpress/core-data';
import type {Option} from '@wordpress/dataviews';
import {asyncSelectEntities} from '../CustomFilter/useAsyncCampaigns';

/**
 * The DataViews filter lists every option at once, so it loads one large page.
 */
const OPTIONS_PER_PAGE = 100;

/**
 * Loads the options of a campaign or form filter through the same core-data entities the old
 * filter selects used. Users who can edit forms also see draft forms.
 *
 * @since TBD
 */
export async function loadEntityElements(entityName: 'campaign' | 'form'): Promise<Option[]> {
    const entity = asyncSelectEntities[entityName];

    const canEdit = entity.editorListQuery
        ? // @ts-ignore
          await resolveSelect(coreStore).canUser('create', entity.name).catch(() => false)
        : false;

    // @ts-ignore
    const records: any[] = (await resolveSelect(coreStore).getEntityRecords(entity.kind, entity.name, {
        ...entity.listQuery,
        ...(canEdit && entity.editorListQuery),
        per_page: OPTIONS_PER_PAGE,
    })) ?? [];

    return records.map((record) => ({value: record.id, label: entity.getTitle(record)}));
}

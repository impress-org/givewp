import {__} from '@wordpress/i18n';
import type {Field, Operator, Option, View} from '@wordpress/dataviews';
import type {FilterByGroupedOptions, FilterConfig, ListTableItem} from '../ListTablePage/types';
import {filterByGroups, filterByValues, filterFieldId, viewFilterValue} from './viewToParameters';

/**
 * Loads the options of a campaign or form filter.
 *
 * @since TBD
 */
export type LoadEntityElements = (entity: 'campaign' | 'form') => Promise<Option[]>;

/**
 * The filter id of test mode, set by the header toggle or its chip.
 *
 * @since TBD
 */
export const TEST_MODE_FILTER = 'testMode';

const toElements = (options: Array<{text: string; value: string}> = []): Option[] =>
    options.map((option) => ({value: option.value, label: option.text}));

/**
 * A field that only filters: it is never a column, so it cannot be hidden, sorted or rendered.
 */
function filterField(
    id: string,
    label: string,
    operator: Operator,
    elements: Option[],
    getElements?: () => Promise<Option[]>
): Field<ListTableItem> {
    return {
        id: filterFieldId(id),
        label,
        elements,
        ...(getElements && {getElements}),
        filterBy: {operators: [operator]},
        enableHiding: false,
        enableSorting: false,
        render: () => null,
    };
}

function groupField(group: FilterByGroupedOptions): Field<ListTableItem> {
    if (group.type === 'toggle') {
        const [option] = group.options;

        return filterField(group.id, option?.text ?? group.name, 'is', [
            {value: option?.value, label: __('Yes', 'give')},
        ]);
    }

    return filterField(group.id, group.name, group.type === 'checkbox' ? 'isAny' : 'is', toElements(group.options));
}

/**
 * Turns the screen's filter settings into DataViews filter fields, so every applied filter shows as a
 * chip the admin can remove.
 *
 * - `select`: its first option is the "all" default, so it is not offered as a value.
 * - `campaignselect` / `formselect`: options load when the filter opens.
 * - `filterby`: one field per group; a group hidden by the current values is left out.
 * - `hidden`: a field only while a link into the screen has set it; in content mode it is table
 *   scope and never a field.
 * - The test-mode `checkbox` (named `toggle`) is a `testMode` field, so test mode shows as a chip; the
 *   header toggle stays and edits the same filter.
 * - `search` is DataViews' own search box.
 *
 * @since TBD
 */
export function filterSettingsToFields(
    filterSettings: FilterConfig[],
    view: View,
    options: {contentMode: boolean; loadEntityElements: LoadEntityElements}
): {fields: Field<ListTableItem>[]; searchParam: string | null} {
    const fields: Field<ListTableItem>[] = [];
    const values = filterByValues(view, filterSettings);
    let searchParam: string | null = null;

    filterSettings.forEach((filter) => {
        switch (filter.type) {
            case 'search':
                searchParam = filter.name;
                break;
            case 'select':
                fields.push(
                    filterField(filter.name, filter.text ?? filter.name, 'is', toElements(filter.options?.slice(1)))
                );
                break;
            case 'campaignselect':
            case 'formselect': {
                const entity = filter.type === 'campaignselect' ? 'campaign' : 'form';
                const label = entity === 'campaign' ? __('Campaign', 'give') : __('Form', 'give');
                const staticElements = toElements(filter.options);

                fields.push(
                    filterField(filter.name, label, 'is', staticElements, async () => [
                        ...staticElements,
                        ...(await options.loadEntityElements(entity)),
                    ])
                );
                break;
            }
            case 'checkbox':
                if (filter.name === 'toggle') {
                    fields.push(
                        filterField(TEST_MODE_FILTER, __('Test mode', 'give'), 'is', [
                            {value: true, label: __('On', 'give')},
                        ])
                    );
                }
                break;
            case 'hidden': {
                const value = viewFilterValue(view, filter.name);

                if (!options.contentMode && value !== undefined && value !== '') {
                    fields.push(
                        filterField(filter.name, filter.text ?? filter.name, 'is', [{value, label: String(value)}])
                    );
                }
                break;
            }
        }
    });

    filterByGroups(filterSettings)
        .filter((group) => !group.isVisible || group.isVisible(values))
        .forEach((group) => fields.push(groupField(group)));

    return {fields, searchParam};
}

import type {Filter} from '@wordpress/dataviews';
import {__} from '@wordpress/i18n';
import {filterFieldId} from '../adapters/viewToParameters';
import type {FilterConfig, TestModeToggle} from '../ListTablePage/types';
import type {FilterDeclaration, FilterOption} from './types';

/**
 * The name of the old filter setting that adds the test-mode switch to the page header.
 */
const TEST_MODE_SETTING = 'toggle';

/**
 * What the old filter settings of a screen mean in the declaration model.
 *
 * @since TBD
 */
export type LegacyFilterDeclarations = {
    filters: FilterDeclaration[];
    /** Fixed request parameters: the hidden filters of a list in a details page tab. */
    scope: Record<string, string>;
    testModeToggle?: TestModeToggle;
    /** Filters on before the admin changes anything: a standalone hidden filter's own option. */
    defaultFilters: Filter[];
};

const toOptions = (options: Array<{text: string; value: string}> = []): FilterOption[] =>
    options.map((option) => ({value: option.value, label: option.text}));

/**
 * The declaration of one old filter setting, or nothing for a type that has none.
 */
function declarationOf(filter: FilterConfig): FilterDeclaration | undefined {
    switch (filter.type) {
        case 'select': {
            // The first option is the "all" default: it is what is sent while nothing is chosen.
            const [all, ...choices] = filter.options ?? [];

            return {
                kind: 'options',
                id: filter.name,
                label: filter.text ?? filter.name,
                isPrimary: true,
                multiple: false,
                options: toOptions(choices),
                parameter: filter.name,
                ...(all && {whenEmpty: {[filter.name]: all.value}}),
                legacyUrlKey: filter.name,
            };
        }
        case 'campaignselect':
        case 'formselect': {
            const isCampaign = filter.type === 'campaignselect';
            const allowNone = (filter.options ?? []).some((option) => option.value === 'none');

            return {
                kind: 'entity',
                id: filter.name,
                label: isCampaign ? __('Campaign', 'give') : __('Form', 'give'),
                isPrimary: true,
                source: isCampaign ? 'campaign' : 'form',
                multiple: false,
                parameter: filter.name,
                ...(allowNone && {allowNone: true}),
                legacyUrlKey: filter.name,
            };
        }
        case 'search':
            return {
                kind: 'search',
                id: 'search',
                label: filter.text ?? __('Search', 'give'),
                hint: '',
                parameter: filter.name,
            };
        default:
            return undefined;
    }
}

/**
 * Maps the old `filterSettings` of a list screen onto filter declarations, so a screen that still
 * passes them renders through the same filter bar, URL rules and request builder as every other one.
 *
 * - `select`: a single options filter; its first option is the "all" default, sent while empty.
 * - `campaignselect` / `formselect`: a single campaign or form filter; "No campaign" when the setting
 *   has the `none` option.
 * - `search`: the labeled search box, with no hint.
 * - `checkbox` named `toggle`: the test-mode switch and chip.
 * - `hidden`: fixed scope in content mode; on a standalone screen, a single options filter whose one
 *   choice is its option, on by default. A standalone hidden filter without an option is skipped.
 * - Any other type, such as the removed `filterby`, is skipped without an error.
 *
 * @deprecated Kept only so add-ons that still pass `filterSettings` to `ListTablePage` keep working
 * (CQ-006). Pass `filters`, `scope` and `testModeToggle` instead.
 *
 * @since TBD
 */
export function filterSettingsToDeclarations(
    filterSettings: FilterConfig[],
    contentMode: boolean
): LegacyFilterDeclarations {
    const result: LegacyFilterDeclarations = {filters: [], scope: {}, defaultFilters: []};

    filterSettings.forEach((filter) => {
        if (filter.type === 'checkbox') {
            if (filter.name === TEST_MODE_SETTING) {
                result.testModeToggle = {
                    label: filter.text ?? __('Test mode', 'give'),
                    ariaLabel: filter.ariaLabel ?? filter.text ?? __('Test mode', 'give'),
                };
            }

            return;
        }

        if (filter.type === 'hidden') {
            const option = filter.options?.[0];

            if (!option?.value) {
                return;
            }

            if (contentMode) {
                result.scope[filter.name] = String(option.value);

                return;
            }

            result.filters.push({
                kind: 'options',
                id: filter.name,
                label: filter.text ?? filter.name,
                multiple: false,
                options: toOptions([option]),
                parameter: filter.name,
                legacyUrlKey: filter.name,
            });
            result.defaultFilters.push({field: filterFieldId(filter.name), operator: 'is', value: option.value});

            return;
        }

        const declaration = declarationOf(filter);

        if (declaration) {
            result.filters.push(declaration);
        }
    });

    return result;
}

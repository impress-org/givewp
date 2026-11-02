import type {FilterConfig} from '../ListTablePage/types';
import {filterSettingsToDeclarations} from './legacyFilterSettings';

const statusOptions = [
    {value: 'any', text: 'All'},
    {value: 'publish', text: 'Published'},
    {value: 'draft', text: 'Draft'},
];

describe('filterSettingsToDeclarations', () => {
    it('maps a select onto a single options filter without its first ("all") option', () => {
        const {filters} = filterSettingsToDeclarations(
            [{name: 'status', type: 'select', text: 'Status', options: statusOptions}],
            false
        );

        expect(filters).toEqual([
            {
                kind: 'options',
                id: 'status',
                label: 'Status',
                isPrimary: true,
                multiple: false,
                options: [
                    {value: 'publish', label: 'Published'},
                    {value: 'draft', label: 'Draft'},
                ],
                parameter: 'status',
                whenEmpty: {status: 'any'},
                legacyUrlKey: 'status',
            },
        ]);
    });

    it('maps a campaignselect onto a single campaign filter, with "No campaign" when it has the none option', () => {
        const withNone = filterSettingsToDeclarations(
            [{name: 'campaignId', type: 'campaignselect', options: [{value: 'none', text: 'No campaign'}]}],
            false
        );
        const withoutNone = filterSettingsToDeclarations([{name: 'campaignId', type: 'campaignselect'}], false);

        expect(withNone.filters).toEqual([
            {
                kind: 'entity',
                id: 'campaignId',
                label: 'Campaign',
                isPrimary: true,
                source: 'campaign',
                multiple: false,
                parameter: 'campaignId',
                allowNone: true,
                legacyUrlKey: 'campaignId',
            },
        ]);
        expect(withoutNone.filters[0]).not.toHaveProperty('allowNone');
    });

    it('maps a formselect onto a single form filter', () => {
        const {filters} = filterSettingsToDeclarations([{name: 'formId', type: 'formselect'}], false);

        expect(filters).toEqual([
            {
                kind: 'entity',
                id: 'formId',
                label: 'Form',
                isPrimary: true,
                source: 'form',
                multiple: false,
                parameter: 'formId',
                legacyUrlKey: 'formId',
            },
        ]);
    });

    it('maps a search onto the search box, labeled with its text and no hint', () => {
        const {filters} = filterSettingsToDeclarations(
            [{name: 'search', type: 'search', text: 'Search by name or ID'}],
            false
        );

        expect(filters).toEqual([
            {kind: 'search', id: 'search', label: 'Search by name or ID', hint: '', parameter: 'search'},
        ]);
    });

    it('turns the checkbox named toggle into the test-mode switch, not a filter', () => {
        const result = filterSettingsToDeclarations(
            [{name: 'toggle', type: 'checkbox', text: 'Test', ariaLabel: 'View test donations'}],
            false
        );

        expect(result.filters).toEqual([]);
        expect(result.testModeToggle).toEqual({label: 'Test', ariaLabel: 'View test donations'});
    });

    it('gives no test-mode switch without the toggle setting', () => {
        expect(filterSettingsToDeclarations([], false).testModeToggle).toBeUndefined();
    });

    it('turns a hidden filter in content mode into fixed scope', () => {
        const result = filterSettingsToDeclarations(
            [{name: 'campaignId', type: 'hidden', options: [{value: '7', text: 'All Campaign Forms'}]}],
            true
        );

        expect(result.filters).toEqual([]);
        expect(result.scope).toEqual({campaignId: '7'});
    });

    it('maps a standalone hidden filter onto a single options filter whose one choice is its option, on by default', () => {
        const result = filterSettingsToDeclarations(
            [{name: 'campaignId', type: 'hidden', text: 'Campaign', options: [{value: '7', text: 'Spring'}]}],
            false
        );

        expect(result.scope).toEqual({});
        expect(result.filters).toEqual([
            {
                kind: 'options',
                id: 'campaignId',
                label: 'Campaign',
                multiple: false,
                options: [{value: '7', label: 'Spring'}],
                parameter: 'campaignId',
                legacyUrlKey: 'campaignId',
            },
        ]);
        expect(result.defaultFilters).toEqual([{field: 'filter-campaignId', operator: 'is', value: '7'}]);
    });

    it('skips an unknown type such as the removed filterby, and a checkbox that is not the toggle', () => {
        const settings = [
            {name: 'filterBy', type: 'filterby'},
            {name: 'other', type: 'checkbox'},
        ] as unknown as FilterConfig[];

        expect(filterSettingsToDeclarations(settings, false)).toEqual({filters: [], scope: {}, defaultFilters: []});
    });

    it('keeps the order of the settings', () => {
        const {filters} = filterSettingsToDeclarations(
            [
                {name: 'campaignId', type: 'campaignselect'},
                {name: 'search', type: 'search'},
                {name: 'status', type: 'select', options: statusOptions},
            ],
            false
        );

        expect(filters.map((filter) => filter.id)).toEqual(['campaignId', 'search', 'status']);
    });
});

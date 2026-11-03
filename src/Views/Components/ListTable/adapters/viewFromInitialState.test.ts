import type {FilterConfig} from '../ListTablePage/types';
import {defaultFilters, scopeFromFilterSettings} from './viewFromInitialState';
import {filterFieldId} from './viewToParameters';

const filters: FilterConfig[] = [
    {
        name: 'status',
        type: 'select',
        options: [
            {value: 'any', text: 'All'},
            {value: 'trash', text: 'Trash'},
        ],
    },
    {name: 'search', type: 'search'},
    {name: 'toggle', type: 'checkbox', text: 'Test'},
    {name: 'campaignId', type: 'campaignselect'},
    {name: 'donor', type: 'hidden', options: [{value: '12', text: ''}]},
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: [
            {id: 'status', apiParam: 'status', name: 'Status', type: 'checkbox', defaultValue: 'publish', options: []},
            {id: 'period', apiParam: 'start', name: 'Period', type: 'radio', defaultValue: ['30'], options: []},
            {id: 'showTrashed', apiParam: 'status', name: 'Trash', type: 'toggle', options: []},
        ],
    },
];

describe('defaultFilters', () => {
    it("applies each group's default and the hidden filter's own option", () => {
        expect(defaultFilters(filters)).toEqual([
            {field: filterFieldId('donor'), operator: 'is', value: '12'},
            {field: filterFieldId('status'), operator: 'isAny', value: ['publish']},
            {field: filterFieldId('period'), operator: 'is', value: '30'},
        ]);
    });

    it('leaves hidden filters out of the view in content mode', () => {
        expect(defaultFilters(filters, {contentMode: true}).map((filter) => filter.field)).not.toContain(
            filterFieldId('donor')
        );
    });

    it('turns test mode on for a site in test mode, only on a screen with the toggle', () => {
        const testMode = {field: filterFieldId('testMode'), operator: 'is', value: true};

        expect(defaultFilters(filters, {testMode: true})).toContainEqual(testMode);
        expect(defaultFilters(filters)).not.toContainEqual(testMode);
        expect(defaultFilters([], {testMode: true})).toEqual([]);
    });

    it('never reads the address', () => {
        window.history.replaceState(null, '', '/wp-admin/edit.php?status=trash&campaignId=4&search=jane');

        expect(defaultFilters(filters).map((filter) => filter.field)).toEqual([
            filterFieldId('donor'),
            filterFieldId('status'),
            filterFieldId('period'),
        ]);
    });
});

describe('scopeFromFilterSettings', () => {
    const scoped: FilterConfig[] = [
        {name: 'campaignId', type: 'hidden', options: [{value: '7', text: ''}]},
        {name: 'search', type: 'search'},
    ];

    it('sends hidden filters as table scope in content mode only', () => {
        expect(scopeFromFilterSettings(scoped, true)).toEqual({campaignId: '7'});
        expect(scopeFromFilterSettings(scoped, false)).toEqual({});
    });

    it('does not read the host page address', () => {
        window.history.replaceState(null, '', '/wp-admin/edit.php?campaignId=9');

        expect(scopeFromFilterSettings(scoped, true)).toEqual({campaignId: '7'});
    });
});

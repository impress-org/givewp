import type {View} from '@wordpress/dataviews';
import type {FilterConfig} from '../ListTablePage/types';
import {filterSettingsToFields} from './filterSettingsToFields';
import {filterFieldId} from './viewToParameters';

const filters: FilterConfig[] = [
    {name: 'status', type: 'select', text: 'Status', options: [{value: 'any', text: 'All'}, {value: 'trash', text: 'Trash'}]},
    {name: 'search', type: 'search', text: 'Search donations'},
    {name: 'campaignId', type: 'campaignselect', options: [{value: 'none', text: 'No campaign'}]},
    {name: 'formId', type: 'formselect'},
    {name: 'toggle', type: 'checkbox', text: 'Test'},
    {name: 'donor', type: 'hidden'},
    {
        name: 'filterBy',
        type: 'filterby',
        groupedOptions: [
            {id: 'showTrashed', apiParam: 'status', name: 'Status', type: 'toggle', options: [{text: 'Show trashed', value: 'trash'}]},
            {
                id: 'statuses',
                apiParam: 'status',
                name: 'Status',
                type: 'checkbox',
                isVisible: (values) => !values?.status?.includes('trash'),
                options: [{text: 'Completed', value: 'publish'}],
            },
            {id: 'period', apiParam: 'start', name: 'Period', type: 'radio', options: [{text: 'Last 7 days', value: 'x'}]},
        ],
    },
];

const view = (filtersApplied: View['filters'] = []): View =>
    ({type: 'table', page: 1, perPage: 30, search: '', filters: filtersApplied}) as View;

const options = {contentMode: false, loadEntityElements: jest.fn(async () => [{value: 3, label: 'Spring'}])};

const fieldById = (fields, id: string) => fields.find((field) => field.id === filterFieldId(id));

describe('filterSettingsToFields', () => {
    it('reports the search parameter and makes no field for search or the test-mode toggle', () => {
        const {fields, searchParam} = filterSettingsToFields(filters, view(), options);

        expect(searchParam).toBe('search');
        expect(fieldById(fields, 'search')).toBeUndefined();
        expect(fieldById(fields, 'toggle')).toBeUndefined();
    });

    it('offers select options except the first, "all" one, with the is operator', () => {
        const status = fieldById(filterSettingsToFields(filters, view(), options).fields, 'status');

        expect(status.elements).toEqual([{value: 'trash', label: 'Trash'}]);
        expect(status.filterBy).toEqual({operators: ['is']});
        expect(status.enableHiding).toBe(false);
    });

    it('loads campaign options after its own static options', async () => {
        const campaign = fieldById(filterSettingsToFields(filters, view(), options).fields, 'campaignId');

        await expect(campaign.getElements()).resolves.toEqual([
            {value: 'none', label: 'No campaign'},
            {value: 3, label: 'Spring'},
        ]);
        expect(options.loadEntityElements).toHaveBeenCalledWith('campaign');
    });

    it('maps filter-by groups: checkbox to isAny, radio and toggle to is', () => {
        const {fields} = filterSettingsToFields(filters, view(), options);

        expect(fieldById(fields, 'statuses').filterBy).toEqual({operators: ['isAny']});
        expect(fieldById(fields, 'period').filterBy).toEqual({operators: ['is']});
        expect(fieldById(fields, 'showTrashed')).toMatchObject({label: 'Show trashed', filterBy: {operators: ['is']}});
    });

    it('leaves out a filter-by group the current values hide', () => {
        const {fields} = filterSettingsToFields(
            filters,
            view([{field: filterFieldId('showTrashed'), operator: 'is', value: 'trash'}]),
            options
        );

        expect(fieldById(fields, 'statuses')).toBeUndefined();
    });

    it('makes a link-driven hidden filter a field only while it is applied', () => {
        expect(fieldById(filterSettingsToFields(filters, view(), options).fields, 'donor')).toBeUndefined();

        const donor = fieldById(
            filterSettingsToFields(filters, view([{field: filterFieldId('donor'), operator: 'is', value: '12'}]), options)
                .fields,
            'donor'
        );

        expect(donor.elements).toEqual([{value: '12', label: '12'}]);
    });

    it('never makes a field of a hidden filter in content mode', () => {
        const {fields} = filterSettingsToFields(
            filters,
            view([{field: filterFieldId('donor'), operator: 'is', value: '12'}]),
            {...options, contentMode: true}
        );

        expect(fieldById(fields, 'donor')).toBeUndefined();
    });
});

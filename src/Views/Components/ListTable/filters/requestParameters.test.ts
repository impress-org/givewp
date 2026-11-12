import type {ListTableView} from '../state/types';
import {filterRequestParameters} from './requestParameters';
import type {
    DateFilterDeclaration,
    EntityFilterDeclaration,
    FilterDeclaration,
    OptionsFilterDeclaration,
    SearchFilterDeclaration,
} from './types';

const status: OptionsFilterDeclaration = {
    id: 'status',
    label: 'Status',
    kind: 'options',
    multiple: true,
    options: 'statuses',
    parameter: 'status',
    isPrimary: true,
};
const campaignsStatus: OptionsFilterDeclaration = {...status, parameter: 'statuses', whenEmpty: {status: 'any'}};
const campaign: EntityFilterDeclaration = {
    id: 'campaign',
    label: 'Campaign',
    kind: 'entity',
    source: 'campaign',
    multiple: true,
    parameter: 'campaignId',
    allowNone: true,
};
const donor: EntityFilterDeclaration = {
    id: 'donor',
    label: 'Donor',
    kind: 'entity',
    source: 'donor',
    multiple: false,
    parameter: 'donor',
};
const date: DateFilterDeclaration = {id: 'date', label: 'Date', kind: 'date', parameters: {start: 'start', end: 'end'}};
const search: SearchFilterDeclaration = {
    kind: 'search',
    id: 'search',
    label: 'Search donors',
    hint: 'Name or email',
    parameter: 'search',
};
const declarations: FilterDeclaration[] = [status, campaign, donor, date, search];

const viewOf = (filters: Array<[string, unknown]>, searchText?: string): ListTableView =>
    ({
        type: 'table',
        search: searchText,
        filters: filters.map(([id, value]) => ({field: `filter-${id}`, operator: 'is', value})),
    } as ListTableView);
const today = '2026-10-20';

describe('filterRequestParameters', () => {
    it('sends a multiple options value as a list', () =>
        expect(filterRequestParameters(declarations, viewOf([['status', ['publish', 'pending']]]), today)).toEqual({
            status: ['publish', 'pending'],
        }));

    it('sends the whenEmpty parameters while the filter is empty', () => {
        expect(filterRequestParameters([campaignsStatus], viewOf([]), today)).toEqual({status: 'any'});
        expect(filterRequestParameters([campaignsStatus], viewOf([['status', []]]), today)).toEqual({status: 'any'});
        expect(filterRequestParameters([campaignsStatus], viewOf([['status', ['draft']]]), today)).toEqual({
            statuses: ['draft'],
        });
    });

    it('sends a single entity as text', () =>
        expect(filterRequestParameters(declarations, viewOf([['donor', '123']]), today)).toEqual({donor: '123'}));

    it('sends none as one value', () =>
        expect(filterRequestParameters(declarations, viewOf([['campaign', ['none']]]), today)).toEqual({
            campaignId: 'none',
        }));

    it('keeps several campaigns as a list', () =>
        expect(filterRequestParameters(declarations, viewOf([['campaign', ['12', '15']]]), today)).toEqual({
            campaignId: ['12', '15'],
        }));

    it('turns a preset into whole-day bounds', () =>
        expect(filterRequestParameters(declarations, viewOf([['date', '30']]), today)).toEqual({
            start: '2026-09-21T00:00:00',
            end: '2026-10-20T23:59:59',
        }));

    it('sends only the open start', () =>
        expect(filterRequestParameters(declarations, viewOf([['date', '2026-09-01_']]), today)).toEqual({
            start: '2026-09-01T00:00:00',
        }));

    it('uses the declared date parameter names', () =>
        expect(
            filterRequestParameters(
                [{...date, parameters: {start: 'from', end: 'to'}}],
                viewOf([['date', '_2026-09-15']]),
                today
            )
        ).toEqual({to: '2026-09-15T23:59:59'}));

    it('sends the search text', () =>
        expect(filterRequestParameters(declarations, viewOf([], 'smi'), today)).toEqual({search: 'smi'}));

    it('leaves out a secondary entry with no value', () =>
        expect(filterRequestParameters(declarations, viewOf([['donor', undefined]]), today)).toEqual({}));

    it('ignores a filter that is not declared', () =>
        expect(filterRequestParameters(declarations, viewOf([['other', 'x']]), today)).toEqual({}));

    it('ignores a date value that is not valid', () =>
        expect(filterRequestParameters(declarations, viewOf([['date', 'nope']]), today)).toEqual({}));
});

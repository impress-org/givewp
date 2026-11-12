import {MAX_FILTER_VALUES, parseFilterValue, serializeFilterValue} from './filterValue';
import type {
    DateFilterDeclaration,
    EntityFilterDeclaration,
    OptionsFilterDeclaration,
    StatusVocabularyEntry,
} from './types';

const statuses: StatusVocabularyEntry[] = [
    {value: 'publish', label: 'Completed', filterable: true},
    {value: 'pending', label: 'Pending', filterable: true},
    {value: 'trash', label: 'Trash', filterable: true},
    {value: 'internal', label: 'Internal', filterable: false},
];
const status: OptionsFilterDeclaration = {
    id: 'status',
    label: 'Status',
    kind: 'options',
    multiple: true,
    options: 'statuses',
    parameter: 'status',
    exclusiveValues: ['trash'],
    isPrimary: true,
};
const campaign: EntityFilterDeclaration = {
    id: 'campaign',
    label: 'Campaign',
    kind: 'entity',
    source: 'campaign',
    multiple: true,
    parameter: 'campaignId',
    allowNone: true,
    isPrimary: true,
};
const donor: EntityFilterDeclaration = {
    id: 'donor',
    label: 'Donor',
    kind: 'entity',
    source: 'donor',
    multiple: false,
    parameter: 'donor',
};
const date: DateFilterDeclaration = {
    id: 'date',
    label: 'Donation date',
    kind: 'date',
    parameters: {start: 'start', end: 'end'},
};
const donationType: OptionsFilterDeclaration = {
    id: 'donationType',
    label: 'Donation type',
    kind: 'options',
    multiple: false,
    options: [
        {value: 'one-time', label: 'One-time'},
        {value: 'recurring', label: 'Recurring'},
    ],
    parameter: 'donationType',
};

describe('filter value text', () => {
    it.each([
        [status, ['publish', 'pending']],
        [campaign, ['12', '15']],
        [campaign, ['none']],
        [donor, '123'],
        [donationType, 'recurring'],
        [date, '30'],
        [date, 'this-month'],
        [date, '2026-09-01_2026-09-15'],
        [date, '2026-09-01_'],
        [date, '_2026-09-15'],
    ])('round trips %j %j', (declaration, value) => {
        const text = serializeFilterValue(declaration, value);

        expect(parseFilterValue(declaration, text ?? '', {statuses})).toEqual({value, corrected: false});
    });

    it('writes nothing for an empty value', () => {
        expect(serializeFilterValue(status, [])).toBeUndefined();
        expect(serializeFilterValue(donor, '')).toBeUndefined();
    });

    it('drops statuses the vocabulary does not offer', () =>
        expect(parseFilterValue(status, 'publish,internal,nope', {statuses})).toEqual({
            value: ['publish'],
            corrected: true,
        }));

    it('keeps an exclusive value alone', () =>
        expect(parseFilterValue(status, 'trash,pending', {statuses})).toEqual({value: ['trash'], corrected: true}));

    it('accepts none only alone and only with allowNone', () => {
        expect(parseFilterValue(campaign, 'none,12', {statuses})).toEqual({value: ['none'], corrected: true});
        expect(parseFilterValue({...campaign, allowNone: false}, 'none', {statuses})).toEqual({corrected: true});
    });

    it('keeps the first 50 ids', () => {
        const ids = Array.from({length: 60}, (_, index) => String(index + 1));

        expect(MAX_FILTER_VALUES).toBe(50);
        expect(parseFilterValue(campaign, ids.join(','), {statuses})).toEqual({
            value: ids.slice(0, 50),
            corrected: true,
        });
    });

    it('writes at most 50 values, so the address and the view agree', () => {
        const ids = Array.from({length: 60}, (_, index) => String(index + 1));

        expect(serializeFilterValue(campaign, ids)).toBe(ids.slice(0, 50).join(','));
    });

    it('accepts an offered option value whatever characters it has', () => {
        const custom: OptionsFilterDeclaration = {
            ...status,
            options: [
                {value: 'v1.2', label: 'Version 1.2'},
                {value: 'two words', label: 'Two words'},
            ],
        };

        expect(parseFilterValue(custom, 'v1.2,two words,other', {statuses})).toEqual({
            value: ['v1.2', 'two words'],
            corrected: true,
        });
    });

    it('rejects ids that are not positive integers', () =>
        expect(parseFilterValue(campaign, '0,-1,abc,7', {statuses})).toEqual({value: ['7'], corrected: true}));

    it('drops duplicates', () =>
        expect(parseFilterValue(campaign, '7,7,8', {statuses})).toEqual({value: ['7', '8'], corrected: true}));

    it('drops parts that are not plain tokens', () =>
        expect(parseFilterValue(status, 'publish,a b', {statuses})).toEqual({value: ['publish'], corrected: true}));

    it('keeps the first valid part of a single filter', () =>
        expect(parseFilterValue(donor, '5,6', {statuses})).toEqual({value: '5', corrected: true}));

    it('keeps a single option from a fixed list', () =>
        expect(parseFilterValue(donationType, 'x,recurring', {statuses})).toEqual({
            value: 'recurring',
            corrected: true,
        }));

    it.each(['31', '2026-02-30_', '2026-09-15_2026-09-01', '_', 'nope'])('drops the date token %s', (token) =>
        expect(parseFilterValue(date, token, {statuses})).toEqual({corrected: true})
    );

    it('reports empty text as corrected', () =>
        expect(parseFilterValue(status, '', {statuses})).toEqual({corrected: true}));
});

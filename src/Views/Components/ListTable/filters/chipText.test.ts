import {resetLocaleData, setLocaleData} from '@wordpress/i18n';
import {MAX_CHIP_LABELS, fallbackLabel, filterChipText} from './chipText';
import type {DateFilterDeclaration, EntityFilterDeclaration, OptionsFilterDeclaration} from './types';

const status: OptionsFilterDeclaration = {
    id: 'status',
    label: 'Status',
    kind: 'options',
    multiple: true,
    options: 'statuses',
    parameter: 'status',
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

const labels: Record<string, string> = {active: 'Active', inactive: 'Inactive', archived: 'Archived', draft: 'Draft'};
const days: Record<string, string> = {'2026-09-01': '1 Sep 2026', '2026-09-15': '15 Sep 2026'};
const helpers = {labelOf: (value: string) => labels[value], formatDate: (ymd: string) => days[ymd]};
const noLabel = {...helpers, labelOf: () => undefined as unknown as string};

describe('filterChipText', () => {
    it('lists three labels at most', () => expect(MAX_CHIP_LABELS).toBe(3));

    it.each([
        [status, undefined, helpers, 'Status'],
        [status, [], helpers, 'Status'],
        [status, ['active', 'draft'], helpers, 'Status: Active, Draft'],
        [donor, '123', noLabel, 'Donor #123'],
        [donor, '123', {...helpers, labelOf: () => 'Jane Doe'}, 'Donor: Jane Doe'],
        [date, '30', helpers, 'Donation date: Last 30 days'],
        [date, '2026-09-01_2026-09-15', helpers, 'Donation date: 1 Sep 2026 – 15 Sep 2026'],
        [date, '2026-09-01_', helpers, 'Donation date: on or after 1 Sep 2026'],
        [date, '_2026-09-15', helpers, 'Donation date: on or before 15 Sep 2026'],
    ])('%#: uses one text for the chip, the tooltip and the name', (declaration, value, given, text) =>
        expect(filterChipText(declaration, value, given)).toEqual({text, fullText: text, accessibleName: text})
    );

    it('collapses past three labels, and the name starts with the chip text and then lists them', () =>
        expect(filterChipText(status, ['active', 'inactive', 'archived', 'draft'], helpers)).toEqual({
            text: 'Status: 4 selected',
            fullText: 'Status: Active, Inactive, Archived, Draft',
            accessibleName: 'Status: 4 selected. Active, Inactive, Archived, Draft',
        }));

    it('keeps the filter name before a label that only shares its first letters', () =>
        expect(filterChipText(donor, '123', {...helpers, labelOf: () => 'Donovan'}).text).toBe('Donor: Donovan'));

    it('drops the filter name when every value is a numbered label', () =>
        expect(filterChipText({...donor, multiple: true}, ['1', '2'], noLabel).text).toBe('Donor #1, Donor #2'));

    it('falls back to a numbered label for an entity without a name', () =>
        expect(fallbackLabel(donor, '123')).toBe('Donor #123'));

    describe('when a translation puts the record id first', () => {
        beforeEach(() => setLocaleData({'%1$s #%2$s': ['#%2$s %1$s']}, 'give'));
        afterEach(() => resetLocaleData(undefined, 'give'));

        it('drops the filter name in front of a numbered label', () =>
            expect(filterChipText(donor, '123', noLabel).text).toBe('#123 Donor'));

        it('drops it for several numbered labels too', () =>
            expect(filterChipText({...donor, multiple: true}, ['1', '2'], noLabel).text).toBe('#1 Donor, #2 Donor'));
    });

    it('keeps the filter name before a real name that starts like a numbered label', () =>
        expect(filterChipText(donor, '123', {...helpers, labelOf: () => 'Donor #5 Smith'}).text).toBe(
            'Donor: Donor #5 Smith'
        ));

    it('keeps the filter name when one value has a name and another is numbered', () =>
        expect(
            filterChipText({...donor, multiple: true}, ['1', '2'], {
                ...helpers,
                labelOf: (item: string) => (item === '1' ? 'Jane Doe' : ''),
            }).text
        ).toBe('Donor: Jane Doe, Donor #2'));
});

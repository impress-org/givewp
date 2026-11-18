import {act, useState} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {Filter} from '@wordpress/dataviews';
import {fetchFilterOptions} from '../filters/filterOptionsApi';
import {resetLabelCache} from '../filters/labelCache';
import type {
    BarFilterDeclaration,
    DateFilterDeclaration,
    EntityFilterDeclaration,
    OptionsFilterDeclaration,
    StatusVocabularyEntry,
} from '../filters/types';
import type {ListTableView} from '../state/types';
import ListTableFilters, {type ListTableFiltersProps} from './index';

jest.mock('../filters/filterOptionsApi', () => ({fetchFilterOptions: jest.fn()}));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = fetchFilterOptions as jest.Mock;

const status: OptionsFilterDeclaration = {
    kind: 'options',
    id: 'status',
    label: 'Status',
    isPrimary: true,
    multiple: true,
    options: 'statuses',
    parameter: 'status',
    exclusiveValues: ['trash'],
};
const campaign: EntityFilterDeclaration = {
    kind: 'entity',
    id: 'campaign',
    label: 'Campaign',
    isPrimary: true,
    source: 'campaign',
    multiple: true,
    parameter: 'campaignId',
    allowNone: true,
};
const date: DateFilterDeclaration = {
    kind: 'date',
    id: 'date',
    label: 'Donation date',
    parameters: {start: 'start', end: 'end'},
};
const paymentType: OptionsFilterDeclaration = {
    kind: 'options',
    id: 'paymentType',
    label: 'Payment type',
    multiple: true,
    options: [
        {value: 'card', label: 'Card'},
        {value: 'bank', label: 'Bank'},
    ],
    parameter: 'paymentType',
};

const statuses: StatusVocabularyEntry[] = [
    {value: 'publish', label: 'Completed', filterable: true},
    {value: 'processing', label: 'Processing', filterable: false},
    {value: 'pending', label: 'Pending', filterable: true},
    {value: 'trash', label: 'Trash', filterable: true},
];

const baseView: ListTableView = {type: 'table', page: 1, perPage: 30, search: '', filters: []};

let container: HTMLDivElement;
let root: Root;
let currentView: ListTableView;
const onChangeView = jest.fn();
const onReset = jest.fn();

type HarnessProps = Partial<ListTableFiltersProps> & {initial?: Filter[]};

// Keeps the view like the shell does, so the bar re-renders with each change.
function Harness({initial = [], ...props}: HarnessProps) {
    const [view, setView] = useState<ListTableView>({...baseView, filters: initial});
    currentView = view;

    return (
        <ListTableFilters
            declarations={[status, campaign, date, paymentType]}
            view={view}
            onChangeView={(next) => {
                onChangeView(next);
                setView(next);
            }}
            statuses={statuses}
            canReset={false}
            onReset={onReset}
            {...props}
        />
    );
}

const render = (props: HarnessProps = {}) => act(() => root.render(<Harness {...props} />));
// The first child is the chip text; a focused chip also holds its tooltip.
const chipText = (chip: HTMLButtonElement) => chip.firstChild?.textContent;
const chips = () =>
    Array.from(container.querySelectorAll<HTMLButtonElement>('.givewp-filter-chip__toggle')).map(chipText);
const chipNamed = (start: string) =>
    Array.from(container.querySelectorAll<HTMLButtonElement>('.givewp-filter-chip__toggle')).find((chip) =>
        chipText(chip)?.startsWith(start)
    )!;
const buttonLabeled = (label: string) => container.querySelector<HTMLButtonElement>(`button[aria-label="${label}"]`);
const addFilterButton = () => container.querySelector<HTMLButtonElement>('.givewp-list-table-filters__add button');
const menuItems = () => Array.from(document.querySelectorAll<HTMLButtonElement>('[role="menuitem"]'));
const popover = () => document.querySelector('.givewp-filter-chip__content');
const checkboxNamed = (name: string) =>
    Array.from(document.querySelectorAll<HTMLInputElement>('input[type="checkbox"]')).find(
        (input) => document.querySelector(`label[for="${input.id}"]`)?.textContent === name
    )!;
const click = (element?: HTMLElement | null) => act(() => element?.click());
const entry = (id: string) => currentView.filters?.find((filter) => filter.field === `filter-${id}`);

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    onChangeView.mockClear();
    onReset.mockClear();
    mockedFetch.mockReset();
    mockedFetch.mockResolvedValue({options: [], hasMore: false});
    resetLabelCache();
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('ListTableFilters', () => {
    it('renders primary filters and no "Add filter" when none are secondary', async () => {
        await render({declarations: [status, campaign]});

        expect(chips()).toEqual(['Status', 'Campaign']);
        expect(addFilterButton()).toBeNull();
    });

    it('"Add filter" lists only secondary filters not yet in the bar', async () => {
        await render({initial: [{field: 'filter-date', operator: 'is', value: '30'}]});

        await click(addFilterButton());

        expect(menuItems().map((item) => item.textContent)).toEqual(['Payment type']);
    });

    it('picking from "Add filter" adds an empty entry and opens its popover', async () => {
        await render();

        await click(addFilterButton());
        await click(menuItems().find((item) => item.textContent === 'Payment type'));

        expect(entry('paymentType')).toEqual({field: 'filter-paymentType', operator: 'isAny', value: undefined});
        expect(chips()).toContain('Payment type');
        expect(popover()).not.toBeNull();
        expect(checkboxNamed('Card')).toBeDefined();
    });

    it('opens an added filter only once, not again when its chip comes back later', async () => {
        const renderView = (filters: Filter[]) =>
            act(() =>
                root.render(
                    <ListTableFilters
                        declarations={[status, paymentType]}
                        view={{...baseView, filters}}
                        onChangeView={onChangeView}
                        statuses={statuses}
                        canReset={false}
                        onReset={onReset}
                    />
                )
            );
        await renderView([]);
        await click(addFilterButton());
        await click(menuItems().find((item) => item.textContent === 'Payment type'));
        await renderView([{field: 'filter-paymentType', operator: 'isAny', value: undefined}]);

        expect(popover()).not.toBeNull();

        await click(chipNamed('Payment type'));
        // Reset takes the chip away; browser Back brings it back with a value.
        await renderView([]);
        await renderView([{field: 'filter-paymentType', operator: 'isAny', value: ['card']}]);

        expect(chips()).toContain('Payment type: Card');
        expect(popover()).toBeNull();
    });

    it('clearing a secondary filter keeps its chip with no value', async () => {
        await render({initial: [{field: 'filter-paymentType', operator: 'isAny', value: ['card']}]});

        expect(chips()).toContain('Payment type: Card');

        await click(chipNamed('Payment type'));
        await click(checkboxNamed('Card'));

        expect(chips()).toContain('Payment type');
        expect(entry('paymentType')).toEqual({field: 'filter-paymentType', operator: 'isAny', value: undefined});
        expect(buttonLabeled('Remove Payment type')).not.toBeNull();
    });

    it('removing a secondary chip moves focus to "Add filter"', async () => {
        await render({initial: [{field: 'filter-date', operator: 'is', value: '30'}]});

        await click(buttonLabeled('Remove Donation date'));

        expect(entry('date')).toBeUndefined();
        expect(chips()).not.toContain('Donation date');
        expect(document.activeElement).toBe(addFilterButton());
    });

    it('resetting a primary filter keeps its chip and moves focus to it', async () => {
        await render({initial: [{field: 'filter-status', operator: 'isAny', value: ['pending']}]});

        expect(buttonLabeled('Remove Status')).toBeNull();

        await click(buttonLabeled('Reset Status'));

        expect(entry('status')).toBeUndefined();
        expect(chips()).toContain('Status');
        expect(buttonLabeled('Reset Status')).toBeNull();
        expect(document.activeElement).toBe(chipNamed('Status'));
    });

    it('selecting Trash drops the other statuses', async () => {
        await render({initial: [{field: 'filter-status', operator: 'isAny', value: ['publish', 'pending']}]});

        await click(chipNamed('Status'));
        await click(checkboxNamed('Trash'));

        expect(entry('status')?.value).toEqual(['trash']);
        expect(chips()).toContain('Status: Trash');
    });

    it('status options are the filterable vocabulary entries in order', async () => {
        await render();

        await click(chipNamed('Status'));

        const labels = Array.from(document.querySelectorAll('.givewp-options-filter-control label')).map(
            (label) => label.textContent
        );

        expect(labels).toEqual(['Completed', 'Pending', 'Trash']);
    });

    it('names a status that is not offered in the filter from the vocabulary', async () => {
        await render({initial: [{field: 'filter-status', operator: 'isAny', value: ['processing']}]});

        expect(chips()).toContain('Status: Processing');
    });

    it('names a collapsed chip by its visible text and then every value', async () => {
        await render({
            initial: [
                {field: 'filter-status', operator: 'isAny', value: ['publish', 'processing', 'pending', 'trash']},
            ],
        });

        expect(chips()).toContain('Status: 4 selected');
        expect(chipNamed('Status').getAttribute('aria-label')).toBe(
            'Status: 4 selected. Completed, Processing, Pending, Trash'
        );
    });

    it('names a chip with three values or fewer by its visible text only', async () => {
        await render({initial: [{field: 'filter-status', operator: 'isAny', value: ['publish', 'pending']}]});

        expect(chipNamed('Status').getAttribute('aria-label')).toBe('Status: Completed, Pending');
    });

    it('shows "No campaign" on the chip for the none value', async () => {
        await render({initial: [{field: 'filter-campaign', operator: 'isAny', value: ['none']}]});

        expect(chips()).toContain('Campaign: No campaign');
    });

    it('shows a fixed message, never the server text, when the options fail to load', async () => {
        mockedFetch.mockRejectedValue(new Error('SQL error near donor_email'));
        await render();

        await act(async () => chipNamed('Campaign').click());

        const alert = document.querySelector('.givewp-entity-filter-control__error');

        expect(alert?.textContent).toContain('Could not load the options. Try again.');
        expect(alert?.textContent).not.toContain('SQL error');
    });

    it('"Reset" shows only when canReset and calls onReset', async () => {
        const resetButton = () =>
            Array.from(container.querySelectorAll('button')).find((button) => button.textContent === 'Reset');

        await render({canReset: false});

        expect(resetButton()).toBeUndefined();

        await render({canReset: true});
        await click(resetButton());

        expect(onReset).toHaveBeenCalledTimes(1);
    });

    it('filters render in declaration order: primary first', async () => {
        const declarations: BarFilterDeclaration[] = [date, status, paymentType, campaign];

        await render({
            declarations,
            initial: [
                {field: 'filter-paymentType', operator: 'isAny', value: ['bank']},
                {field: 'filter-date', operator: 'is', value: '30'},
            ],
        });

        expect(chips()).toEqual(['Status', 'Campaign', 'Donation date: Last 30 days', 'Payment type: Bank']);
    });

    it('renders nothing when there is nothing to show', async () => {
        await render({declarations: []});

        expect(container.innerHTML).toBe('');
    });
});

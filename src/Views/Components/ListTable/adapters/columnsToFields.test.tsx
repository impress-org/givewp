import {createRoot} from 'react-dom/client';
import {act} from 'react';
import {cellText, columnsToFields, titleFieldId, visibleFieldIds} from './columnsToFields';
import type {Column} from '../ListTablePage/types';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const columns: Column[] = [
    {id: 'id', label: 'ID', sortable: true},
    {id: 'title', label: 'Title', sortable: true, visible: true},
    {id: 'goal', label: 'Goal', sortable: false},
    {id: 'createdAt', label: 'Created', sortable: true, visible: false},
];

describe('columnsToFields', () => {
    it('keeps the server order and maps sortable to enableSorting', () => {
        const fields = columnsToFields(columns);

        expect(fields.map((field) => field.id)).toEqual(['id', 'title', 'goal', 'createdAt']);
        expect(fields.map((field) => field.enableSorting)).toEqual([true, true, false, true]);
        expect(fields.every((field) => field.filterBy === false)).toBe(true);
    });

    it('returns the plain text of an HTML cell as the value', () => {
        const [, title] = columnsToFields(columns);

        expect(title.getValue({item: {id: 1, title: '<a href="#"><strong>Spring</strong> drive</a>'}})).toBe(
            'Spring drive'
        );
    });

    it('renders a column through its column filter when the screen passes one', () => {
        const filter = jest.fn(() => <span>custom cell</span>);
        const data = {items: [], totalItems: 0, totalPages: 0};
        const [, title] = columnsToFields(columns, [{column: 'title', filter}], data);
        const Render = title.render;

        const container = document.createElement('div');

        act(() => createRoot(container).render(<Render item={{id: 3, title: 'x'}} field={title as any} />));

        expect(container.textContent).toBe('custom cell');
        expect(filter).toHaveBeenCalledWith({id: 3, title: 'x'}, columns[1], data);
    });
});

describe('visibleFieldIds', () => {
    it('includes undefined visibility and leaves out hidden columns and the title field', () => {
        expect(visibleFieldIds(columns)).toEqual(['id', 'title', 'goal']);
        expect(visibleFieldIds(columns, 'title')).toEqual(['id', 'goal']);
    });
});

describe('titleFieldId', () => {
    it('prefers the donor, then the title, then the donor information column', () => {
        expect(titleFieldId(columns)).toBe('title');
        expect(titleFieldId([...columns, {id: 'donor', label: 'Donor', sortable: true}])).toBe('donor');
        expect(titleFieldId([{id: 'donorInformation', label: 'Donor', sortable: true}])).toBe('donorInformation');
        expect(titleFieldId([{id: 'id', label: 'ID', sortable: true}])).toBeUndefined();
    });
});

describe('cellText', () => {
    it('turns non-string values into text', () => {
        expect(cellText(12)).toBe('12');
        expect(cellText(null)).toBe('');
        expect(cellText(undefined)).toBe('');
    });
});

import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {View} from '@wordpress/dataviews';
import useListTableSelection from './useListTableSelection';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const view = {type: 'table', page: 1, perPage: 20, fields: []} as unknown as View;
let selected: string[] = [];
let select: (ids: string[]) => void = () => {};
let container: HTMLDivElement;
let root: Root;

function Probe() {
    const {selection, onChangeSelection} = useListTableSelection(view);
    selected = selection;
    select = onChangeSelection;

    return null;
}

const press = () =>
    act(() => {
        document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', bubbles: true, cancelable: true}));
    });

beforeEach(async () => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    await act(() => root.render(<Probe />));
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
    document.body.innerHTML = '';
});

describe('useListTableSelection', () => {
    it('clears the selection on Esc', async () => {
        await act(() => select(['1', '2']));
        expect(selected).toEqual(['1', '2']);

        await press();

        expect(selected).toEqual([]);
    });

    it('keeps the selection when Esc belongs to an open dialog', async () => {
        await act(() => select(['1']));
        const dialog = document.createElement('div');
        dialog.setAttribute('role', 'dialog');
        document.body.appendChild(dialog);

        await press();

        expect(selected).toEqual(['1']);
    });
});

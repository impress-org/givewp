import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {View} from '@wordpress/dataviews';
import {LargePagination} from './ListTablePagination';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;
const onChangeView = jest.fn();
const view = (page = 1): View => ({type: 'table', page, perPage: 30});

const input = () => container.querySelector<HTMLInputElement>('.givewp-list-table__page-input')!;
const buttons = () => [...container.querySelectorAll<HTMLButtonElement>('button')];

const type = (value: string) =>
    act(() => {
        const setValue = Object.getOwnPropertyDescriptor(HTMLInputElement.prototype, 'value')!.set!;
        setValue.call(input(), value);
        input().dispatchEvent(new Event('input', {bubbles: true}));
    });
const enter = () =>
    act(() => {
        input().dispatchEvent(new KeyboardEvent('keydown', {key: 'Enter', bubbles: true}));
    });

const blur = () =>
    act(() => {
        input().dispatchEvent(new FocusEvent('focusout', {bubbles: true}));
    });

const render = (totalPages: number, page = 1) =>
    act(() => root.render(<LargePagination view={view(page)} onChangeView={onChangeView} totalPages={totalPages} />));

beforeEach(() => {
    onChangeView.mockClear();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('LargePagination', () => {
    it('builds no option per page, however many pages there are', async () => {
        await render(33334);

        expect(container.querySelectorAll('option, select')).toHaveLength(0);
        expect(input().max).toBe('33334');
        expect(container.textContent).toContain('33334');
    });

    it('goes to the typed page when Enter is pressed, and not on each key', async () => {
        await render(33334);
        await type('2');
        await type('250');

        expect(onChangeView).not.toHaveBeenCalled();

        await enter();

        expect(onChangeView).toHaveBeenCalledTimes(1);
        expect(onChangeView).toHaveBeenCalledWith({type: 'table', page: 250, perPage: 30});
    });

    it('keeps the typed page between the first and the last page', async () => {
        await render(500);
        await type('99999');
        await enter();

        expect(onChangeView).toHaveBeenLastCalledWith(expect.objectContaining({page: 500}));

        await render(500, 500);
        await type('0');
        await enter();

        expect(onChangeView).toHaveBeenLastCalledWith(expect.objectContaining({page: 1}));
    });

    it('puts the current page back when the box is empty or not a number', async () => {
        await render(500, 7);
        await type('');
        await enter();

        expect(onChangeView).not.toHaveBeenCalled();
        expect(input().value).toBe('7');
    });

    it('does not reload the page that is already shown', async () => {
        await render(500, 7);
        await type('7');
        await enter();

        expect(onChangeView).not.toHaveBeenCalled();
    });

    it('moves one page with the buttons and disables them at the ends', async () => {
        await render(500, 1);
        const [previous, next] = buttons();

        expect(previous.getAttribute('aria-disabled')).toBe('true');
        expect(next.getAttribute('aria-disabled')).toBe('false');

        await act(() => previous.click());

        expect(onChangeView).not.toHaveBeenCalled();

        await act(() => next.click());

        expect(onChangeView).toHaveBeenCalledWith(expect.objectContaining({page: 2}));

        await render(500, 500);

        expect(buttons()[1].getAttribute('aria-disabled')).toBe('true');
    });

    it('goes to the typed page when the box loses focus', async () => {
        await render(500);
        await type('42');

        expect(onChangeView).not.toHaveBeenCalled();

        await blur();

        expect(onChangeView).toHaveBeenCalledTimes(1);
        expect(onChangeView).toHaveBeenCalledWith(expect.objectContaining({page: 42}));
    });

    it('follows a page change that comes from outside', async () => {
        await render(500, 1);
        await render(500, 40);

        expect(input().value).toBe('40');
    });
});

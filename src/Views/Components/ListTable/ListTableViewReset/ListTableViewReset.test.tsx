import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ListTableViewReset from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;
const onConfirm = jest.fn();

const render = (isDefault: boolean) =>
    act(() => root.render(<ListTableViewReset isDefault={isDefault} onConfirm={onConfirm} />));

const buttonNamed = (name: string) =>
    Array.from(document.querySelectorAll('button')).find((button) => button.textContent?.trim() === name);
const resetButton = () => container.querySelector<HTMLButtonElement>('.givewp-list-table-view-reset__button');
const dialog = () => document.querySelector('[role="dialog"]');
const status = () => container.querySelector('[role="status"]');
const click = (button?: HTMLElement | null) => act(() => button?.click());

const openDialog = async () => {
    await render(false);
    resetButton()?.focus();
    await click(resetButton());
};

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    onConfirm.mockClear();
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('ListTableViewReset', () => {
    it('shows no button while the table has its default layout', async () => {
        await render(true);

        expect(resetButton()).toBeNull();
    });

    it('shows a "Reset view" button with a description once the layout changed', async () => {
        await render(false);

        const button = resetButton();
        const description = document.getElementById(button?.getAttribute('aria-describedby') ?? '');

        expect(button?.textContent).toBe('Reset view');
        expect(button?.getAttribute('aria-haspopup')).toBe('dialog');
        expect(description?.textContent).toBe('The columns, density or page size differ from the defaults.');
    });

    it('asks before resetting, in a labeled dialog that takes focus', async () => {
        await openDialog();

        expect(onConfirm).not.toHaveBeenCalled();
        expect(dialog()?.getAttribute('aria-label')).toBe('Reset view');
        expect(dialog()?.getAttribute('aria-modal')).toBe('true');
        expect(dialog()?.contains(document.activeElement)).toBe(true);
    });

    it('resets on confirm, closes the dialog and announces the reset', async () => {
        await openDialog();
        await click(confirmButton());

        expect(onConfirm).toHaveBeenCalledTimes(1);
        expect(dialog()).toBeNull();
        expect(status()?.textContent).toBe('The table view is back to its defaults.');

        // The layout is now the default, so the button goes away and focus stays in its place.
        await render(true);

        expect(resetButton()).toBeNull();
        expect(document.activeElement).toBe(container.querySelector('.givewp-list-table-view-reset'));
    });

    it('does nothing on cancel and returns focus to the button', async () => {
        await openDialog();
        await click(buttonNamed('Cancel'));

        expect(onConfirm).not.toHaveBeenCalled();
        expect(dialog()).toBeNull();
        expect(document.activeElement).toBe(resetButton());
    });

    it('closes with Escape without resetting', async () => {
        await openDialog();
        await act(() => {
            document.dispatchEvent(new KeyboardEvent('keydown', {keyCode: 27, bubbles: true}));
        });

        expect(onConfirm).not.toHaveBeenCalled();
        expect(dialog()).toBeNull();
        expect(document.activeElement).toBe(resetButton());
    });

    it('leaves focus where it is when Escape is pressed while the dialog is closed', async () => {
        await render(false);
        const input = document.createElement('input');
        document.body.appendChild(input);
        input.focus();

        await act(() => {
            document.dispatchEvent(new KeyboardEvent('keydown', {keyCode: 27, bubbles: true}));
        });

        expect(document.activeElement).toBe(input);
        input.remove();
    });
});

function confirmButton() {
    return Array.from(dialog()?.querySelectorAll('button') ?? []).find(
        (button) => button.textContent?.trim() === 'Reset view'
    );
}

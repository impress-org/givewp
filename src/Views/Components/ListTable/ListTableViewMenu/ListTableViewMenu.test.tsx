import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ListTableViewMenu from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

const toggle = () => container.querySelector<HTMLButtonElement>('button[aria-label="More view options"]');
const menuItem = () =>
    Array.from(document.querySelectorAll<HTMLElement>('[role="menuitem"]')).find(
        (item) => item.textContent?.trim() === 'Switch to Legacy View'
    );
const keyDown = (element: Element | null, code: string) =>
    act(() => {
        element?.dispatchEvent(new KeyboardEvent('keydown', {code, key: code, bubbles: true, cancelable: true}));
    });

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('ListTableViewMenu', () => {
    it('renders nothing without items', async () => {
        await act(() => root.render(<ListTableViewMenu />));

        expect(container.innerHTML).toBe('');
    });

    it('shows a labeled menu button that can take keyboard focus', async () => {
        await act(() => root.render(<ListTableViewMenu legacyView={{onSelect: jest.fn()}} />));

        const button = toggle();
        await act(() => button?.focus());

        expect(button).not.toBeNull();
        expect(button?.getAttribute('aria-haspopup')).toBe('true');
        expect(document.activeElement).toBe(button);
    });

    it('opens from the keyboard and offers "Switch to Legacy View" with its accessible name', async () => {
        await act(() =>
            root.render(
                <ListTableViewMenu
                    legacyView={{ariaLabel: 'Switch to the legacy donations table view', onSelect: jest.fn()}}
                />
            )
        );

        await act(() => toggle()?.focus());
        await keyDown(toggle(), 'ArrowDown');

        expect(toggle()?.getAttribute('aria-expanded')).toBe('true');
        expect(document.querySelector('[role="menu"]')?.getAttribute('aria-label')).toBe('More view options');
        expect(menuItem()?.getAttribute('aria-label')).toBe('Switch to the legacy donations table view');

        // The browser moves focus to the first item on open; jsdom has no layout, so it finds no
        // tabbable item. Focus it here, then check the menu keeps arrow-key focus on its items.
        const item = menuItem();
        expect(item?.tagName).toBe('BUTTON');
        await act(() => item?.focus());
        await keyDown(document.activeElement, 'ArrowDown');

        expect(document.activeElement).toBe(menuItem());
    });

    it('calls onSelect and closes the menu when the item is chosen', async () => {
        const onSelect = jest.fn();
        await act(() => root.render(<ListTableViewMenu legacyView={{onSelect}} />));

        await act(() => toggle()?.click());
        await act(() => menuItem()?.click());

        expect(onSelect).toHaveBeenCalledTimes(1);
        expect(menuItem()).toBeUndefined();
    });
});

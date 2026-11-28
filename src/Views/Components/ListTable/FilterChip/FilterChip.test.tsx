import {act, StrictMode} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import FilterChip, {type FilterChipProps} from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;
const onRemove = jest.fn();

const render = (props: Partial<FilterChipProps> = {}) =>
    act(() =>
        root.render(
            <FilterChip
                text="Status: 2"
                accessibleName="Status: 2. Active, Pending"
                hasValue
                canRemove
                removeLabel="Reset"
                onRemove={onRemove}
                {...props}
            >
                {(close) => (
                    <div>
                        <input aria-label="Inside" />
                        <button onClick={close}>Done</button>
                    </div>
                )}
            </FilterChip>
        )
    );

const chip = () => container.querySelector<HTMLButtonElement>('.givewp-filter-chip__toggle')!;
const removeButton = () => container.querySelector<HTMLButtonElement>('.givewp-filter-chip__remove');
const content = () => document.querySelector('.givewp-filter-chip__content');
const click = (element?: HTMLElement | null) => act(() => element?.click());

beforeEach(() => {
    jest.useFakeTimers();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    onRemove.mockClear();
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
    jest.useRealTimers();
});

describe('FilterChip', () => {
    it('opens from the chip and closes with Escape, with focus back on the chip', async () => {
        await render();
        await act(() => chip().focus());

        expect(chip().getAttribute('aria-expanded')).toBe('false');

        // A native button turns Enter into a click; jsdom does not, so the click stands in for Enter.
        await click(chip());

        expect(chip().getAttribute('aria-expanded')).toBe('true');
        expect(content()).not.toBeNull();

        const inside = document.querySelector<HTMLElement>('[aria-label="Inside"]')!;
        await act(() => inside.focus());
        await act(() => {
            inside.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', keyCode: 27, bubbles: true}));
        });
        await act(() => jest.runOnlyPendingTimers());

        expect(content()).toBeNull();
        expect(chip().getAttribute('aria-expanded')).toBe('false');
        expect(document.activeElement).toBe(chip());
    });

    it('closes when the content calls close', async () => {
        await render();
        await click(chip());
        await click(Array.from(document.querySelectorAll('button')).find((b) => b.textContent === 'Done'));

        expect(content()).toBeNull();
    });

    it('shows the accessible name in the tooltip, so the name and the tooltip are one text', async () => {
        await render();

        expect(chip().getAttribute('aria-label')).toBe('Status: 2. Active, Pending');
        expect(chip().textContent).toBe('Status: 2');

        await act(() => {
            chip().dispatchEvent(new MouseEvent('mouseover', {bubbles: true}));
        });
        await act(() => jest.advanceTimersByTime(1000));

        // Newer Tooltips add the tooltip text as a description when it differs from the name, which
        // would read the list twice.
        expect(document.querySelector('.components-tooltip')?.textContent).toBe('Status: 2. Active, Pending');
    });

    it('has a remove button named after removeLabel and the chip that calls onRemove', async () => {
        await render();

        expect(removeButton()?.getAttribute('aria-label')).toMatch(/^Reset filter: /);

        await click(removeButton());

        expect(onRemove).toHaveBeenCalledTimes(1);
    });

    it('has no remove button when canRemove is false', async () => {
        await render({canRemove: false});

        expect(removeButton()).toBeNull();
    });

    it('opens on mount with defaultOpen', async () => {
        await render({defaultOpen: true});
        await act(() => jest.runOnlyPendingTimers());

        expect(content()).not.toBeNull();
        expect(chip().getAttribute('aria-expanded')).toBe('true');
    });

    it('stays open with defaultOpen when effects run twice', async () => {
        // The installed Dropdown may not close on a second click, so count the clicks the chip sends itself.
        const clicks = jest.spyOn(HTMLButtonElement.prototype, 'click');

        await act(() =>
            root.render(
                <StrictMode>
                    <FilterChip
                        text="Status"
                        fullText="Status"
                        hasValue={false}
                        canRemove={false}
                        removeLabel="Remove"
                        onRemove={onRemove}
                        defaultOpen
                    >
                        {() => <div>Inside</div>}
                    </FilterChip>
                </StrictMode>
            )
        );
        await act(() => jest.runOnlyPendingTimers());

        const toggleClicks = clicks.mock.instances.filter((element) =>
            (element as HTMLElement).classList.contains('givewp-filter-chip__toggle')
        );
        clicks.mockRestore();

        expect(toggleClicks).toHaveLength(1);
        expect(chip().getAttribute('aria-expanded')).toBe('true');
        expect(chip().getAttribute('aria-haspopup')).toBe('dialog');
        expect(document.querySelector('.givewp-filter-chip__content')?.closest('[role="dialog"]')).not.toBeNull();
    });

    it('shows a hidden caret after the text on a chip without a value, and keeps the accessible name', async () => {
        await render({text: 'Campaign', accessibleName: 'Campaign', hasValue: false, canRemove: false});

        const caret = chip().querySelector('.givewp-filter-chip__caret');
        expect(caret).not.toBeNull();
        expect(caret!.getAttribute('aria-hidden')).toBe('true');
        expect(caret!.getAttribute('focusable')).toBe('false');
        expect(chip().lastElementChild).toBe(caret);
        expect(chip().textContent).toBe('Campaign');
        expect(chip().getAttribute('aria-label')).toBe('Campaign');
        expect(chip().getAttribute('aria-haspopup')).toBe('dialog');
        expect(chip().getAttribute('aria-expanded')).toBe('false');
    });

    it('shows no caret on a chip that holds a value', async () => {
        await render();

        expect(chip().querySelector('.givewp-filter-chip__caret')).toBeNull();
        expect(chip().textContent).toBe('Status: 2');
    });
});

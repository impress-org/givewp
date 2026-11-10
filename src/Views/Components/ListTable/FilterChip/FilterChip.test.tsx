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

    it('has a remove button named removeLabel that calls onRemove', async () => {
        await render();

        expect(removeButton()?.getAttribute('aria-label')).toBe('Reset');

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

        expect(chip().getAttribute('aria-expanded')).toBe('true');
        expect(chip().getAttribute('aria-haspopup')).toBe('dialog');
        expect(document.querySelector('.givewp-filter-chip__content')?.closest('[role="dialog"]')).not.toBeNull();
    });
});

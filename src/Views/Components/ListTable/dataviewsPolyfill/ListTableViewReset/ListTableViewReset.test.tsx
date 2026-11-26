import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import {speak} from '@wordpress/a11y';
import ListTableViewReset from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

jest.mock('./styles.scss', () => ({}));
jest.mock('@wordpress/a11y', () => ({speak: jest.fn()}));
// The installed `Button` does not know `__next40pxDefaultSize` and React warns; the one WordPress ships does.
jest.mock('@wordpress/components', () => {
    const {createElement, forwardRef} = jest.requireActual('react');
    const actual = jest.requireActual('@wordpress/components');
    const Button = forwardRef(({__next40pxDefaultSize, ...props}: Record<string, unknown>, ref: unknown) =>
        createElement(actual.Button, {...props, ref})
    );
    return {...actual, Button};
});

let container: HTMLDivElement;
let popover: HTMLDivElement;
let root: Root;
const onReset = jest.fn();

// A fake toolbar with the stock toggle, and a fake popover with the stock "Appearance" heading.
const render = (isDefault: boolean) =>
    act(() =>
        root.render(
            <div>
                <button aria-controls="dataviews-view-config-dropdown-1" aria-expanded="false" aria-label="View options" />
                <ListTableViewReset isDefault={isDefault} onReset={onReset} />
            </div>
        )
    );
const toggle = () => container.querySelector('button')!;
const setOpen = (open: boolean) =>
    act(async () => {
        toggle().setAttribute('aria-expanded', String(open));
        await new Promise((resolve) => setTimeout(resolve, 0));
    });
const resetButtons = () => Array.from(popover.querySelectorAll<HTMLButtonElement>('.givewp-view-config-reset button'));

beforeEach(() => {
    jest.clearAllMocks();
    container = document.createElement('div');
    popover = document.createElement('div');
    popover.id = 'dataviews-view-config-dropdown-1';
    popover.innerHTML = '<div class="dataviews-settings-section__sidebar"><h2>Appearance</h2></div>';
    document.body.append(container, popover);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
    popover.remove();
});

describe('ListTableViewReset', () => {
    it('adds no button while the popover is closed', async () => {
        await render(false);

        expect(resetButtons()).toHaveLength(0);
    });

    it('adds one "Reset view" button to the popover heading when it opens, and removes it when it closes', async () => {
        await render(false);
        await setOpen(true);

        expect(resetButtons().map((button) => button.textContent)).toEqual(['Reset view']);
        expect(popover.querySelector('.dataviews-settings-section__sidebar > .givewp-view-config-reset')).not.toBeNull();

        await setOpen(false);
        expect(resetButtons()).toHaveLength(0);
    });

    it('calls onReset and speaks when clicked', async () => {
        await render(false);
        await setOpen(true);
        await act(async () => resetButtons()[0].click());

        expect(onReset).toHaveBeenCalledTimes(1);
        expect(speak).toHaveBeenCalledWith('The table view is back to its defaults.', 'polite');
    });

    it('is disabled, but still focusable, while the view is the default, and does nothing when clicked', async () => {
        await render(true);
        await setOpen(true);
        const button = resetButtons()[0];
        await act(async () => button.click());

        expect(button.getAttribute('aria-disabled')).toBe('true');
        expect(onReset).not.toHaveBeenCalled();
        expect(speak).not.toHaveBeenCalled();
    });
});

import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import HelpTip from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

const button = () => container.querySelector<HTMLButtonElement>('.givewp-help-tip__button')!;
const content = () => document.querySelector('.givewp-help-tip__content');

// React listens to mouseover/mouseout for enter/leave.
const hover = (element: Element, type: 'mouseover' | 'mouseout', related: Element | null = null) =>
    act(() => {
        element.dispatchEvent(new MouseEvent(type, {bubbles: true, relatedTarget: related}));
    });
const focus = () => act(() => button().focus());

beforeEach(async () => {
    jest.useFakeTimers();
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
    await act(() => root.render(<HelpTip label="How search works" text="The full rule." />));
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
    jest.useRealTimers();
});

describe('HelpTip', () => {
    it('is closed and named at first', () => {
        expect(button().getAttribute('aria-label')).toBe('How search works');
        expect(button().getAttribute('aria-expanded')).toBe('false');
        expect(content()).toBeNull();
    });

    it('opens on keyboard focus and describes the button while open', async () => {
        await focus();

        expect(content()?.textContent).toBe('The full rule.');
        expect(content()?.getAttribute('role')).toBe('tooltip');
        expect(button().getAttribute('aria-expanded')).toBe('true');
        expect(button().getAttribute('aria-describedby')).toBe(content()?.id);
    });

    it('opens on hover', async () => {
        await hover(button(), 'mouseover');

        expect(content()).not.toBeNull();
    });

    it('puts the content class on the popover content so it can wrap', async () => {
        await focus();

        expect(content()?.classList.contains('givewp-help-tip__content')).toBe(true);
    });

    it('closes after the delay when the pointer leaves', async () => {
        await hover(button(), 'mouseover');
        await hover(button(), 'mouseout');
        expect(content()).not.toBeNull();

        await act(() => jest.advanceTimersByTime(200));

        expect(content()).toBeNull();
        expect(button().hasAttribute('aria-describedby')).toBe(false);
    });

    it('stays open when the pointer moves onto the text within the delay', async () => {
        await hover(button(), 'mouseover');
        await hover(button(), 'mouseout', content());
        await act(() => jest.advanceTimersByTime(100));
        await hover(content()!, 'mouseover', button());
        await act(() => jest.advanceTimersByTime(500));

        expect(content()).not.toBeNull();
    });

    it('closes on Esc', async () => {
        await focus();

        await act(() => {
            document.dispatchEvent(new KeyboardEvent('keydown', {key: 'Escape', bubbles: true}));
        });

        expect(content()).toBeNull();
        expect(document.activeElement).toBe(button());
    });

    it('closes on blur', async () => {
        await focus();
        await act(() => button().blur());

        expect(content()).toBeNull();
    });

    it('clears a pending close when it unmounts', async () => {
        await hover(button(), 'mouseover');
        await hover(button(), 'mouseout');
        expect(jest.getTimerCount()).toBeGreaterThan(0);

        await act(() => root.render(null));

        expect(jest.getTimerCount()).toBe(0);
    });
});

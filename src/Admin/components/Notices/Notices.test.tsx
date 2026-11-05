import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import Notice from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('Notice', () => {
    it('is a group named by its title', async () => {
        await act(() =>
            root.render(
                <Notice type="info" title="Campaign list">
                    Body
                </Notice>
            )
        );

        const group = container.querySelector('[role="group"]') as HTMLElement;
        const heading = container.querySelector('h3') as HTMLElement;
        expect(heading.textContent).toBe('Campaign list');
        expect(group.getAttribute('aria-labelledby')).toBe(heading.id);
        expect(heading.id).not.toBe('');
    });

    it('is a group named by ariaLabel when there is no title', async () => {
        await act(() =>
            root.render(
                <Notice type="info" ariaLabel="Pro tip">
                    Body
                </Notice>
            )
        );

        const group = container.querySelector('[role="group"]') as HTMLElement;
        expect(group.getAttribute('aria-label')).toBe('Pro tip');
        expect(container.querySelector('h3')).toBeNull();
    });

    it('has no group role when it has no name (existing callers)', async () => {
        await act(() => root.render(<Notice type="info">Body</Notice>));

        expect(container.querySelector('[role="group"]')).toBeNull();
    });

    it('labels the dismiss button, reaches it by Tab, and hides once with one call', async () => {
        const onDismiss = jest.fn();
        await act(() =>
            root.render(
                <Notice type="info" ariaLabel="Pro tip" dismissLabel="Dismiss this tip" dismissHandleClick={onDismiss}>
                    Body
                </Notice>
            )
        );

        const button = container.querySelector('button') as HTMLButtonElement;
        expect(button.getAttribute('aria-label')).toBe('Dismiss this tip');
        expect(button.type).toBe('button');
        expect(button.tabIndex).toBeGreaterThanOrEqual(0);

        await act(() => button.click());

        expect(onDismiss).toHaveBeenCalledTimes(1);
        expect(container.innerHTML).toBe('');
    });

    it('defaults the dismiss label', async () => {
        await act(() =>
            root.render(
                <Notice type="info" dismissHandleClick={jest.fn()}>
                    Body
                </Notice>
            )
        );

        expect(container.querySelector('button')?.getAttribute('aria-label')).toBe('Dismiss notice');
    });

    it('never puts the text "undefined" in the class name', async () => {
        await act(() => root.render(<Notice type="warning">Body</Notice>));
        expect((container.firstElementChild as HTMLElement).className).not.toContain('undefined');

        await act(() =>
            root.render(
                <Notice type="warning" className="extra">
                    Body
                </Notice>
            )
        );
        expect((container.firstElementChild as HTMLElement).className).toContain('extra');
    });
});

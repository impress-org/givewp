import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import ListTableEmptyState from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let root: Root;
let container: HTMLDivElement;

const render = async (element: JSX.Element) => {
    await act(async () => {
        root.render(element);
    });
};

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(() => {
    act(() => root.unmount());
    container.remove();
});

describe('ListTableEmptyState', () => {
    it('is a polite status with a decorative mark above the message', async () => {
        await render(
            <ListTableEmptyState message="No donations match your search or filters." actionLabel="Clear filters" onAction={jest.fn()} />
        );

        const region = container.querySelector('[role="status"]') as HTMLElement;
        const mark = region.querySelector('svg.givewp-give-mark');

        expect(container.querySelector('[role="alert"]')).toBeNull();
        expect(mark?.getAttribute('aria-hidden')).toBe('true');
        expect(region.textContent).toContain('No donations match your search or filters.');
        expect(region.querySelector('button')?.textContent).toBe('Clear filters');
    });

    it('is an alert when it is an error', async () => {
        await render(<ListTableEmptyState isError message="Problem." actionLabel="Try again" onAction={jest.fn()} />);

        const region = container.querySelector('[role="alert"]') as HTMLElement;

        expect(container.querySelector('[role="status"]')).toBeNull();
        expect(region.querySelector('svg.givewp-give-mark')).not.toBeNull();
    });

    it('calls onAction when the button is clicked', async () => {
        const onAction = jest.fn();
        await render(<ListTableEmptyState message="Nothing." actionLabel="Clear filters" onAction={onAction} />);

        await act(async () => {
            container.querySelector('button')?.click();
        });

        expect(onAction).toHaveBeenCalledTimes(1);
    });
});

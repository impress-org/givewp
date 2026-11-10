import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import StatTile from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

let container: HTMLDivElement;
let root: Root;

const render = (element: JSX.Element) => act(() => root.render(element));

beforeEach(() => {
    container = document.createElement('div');
    document.body.appendChild(container);
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
    container.remove();
});

describe('StatTile', () => {
    it('is a toggle button with label, value and pressed state when it filters', async () => {
        const onToggle = jest.fn();
        await render(<StatTile label="One-Time Donations" value="1,204" state="ready" isPressed onToggle={onToggle} />);

        const button = container.querySelector('button') as HTMLButtonElement;

        expect(button.getAttribute('aria-pressed')).toBe('true');
        expect(button.getAttribute('type')).toBe('button');
        expect(button.textContent).toContain('One-Time Donations');
        expect(button.textContent).toContain('1,204');
        expect(button.textContent?.indexOf('One-Time Donations')).toBeLessThan(
            button.textContent?.indexOf('1,204') ?? 0
        );

        await act(() => button.click());

        expect(onToggle).toHaveBeenCalledTimes(1);
    });

    it('shows a decorative filter icon on a tile that filters, but not on a plain tile or Total', async () => {
        await render(<StatTile label="Donations" value="1" state="ready" onToggle={jest.fn()} />);
        const icon = container.querySelector('button svg.givewp-stat-tile__filter-icon');

        expect(icon).not.toBeNull();
        expect(icon?.getAttribute('aria-hidden')).toBe('true');

        await render(<StatTile label="Total" value="1" state="ready" announcePressed={false} onToggle={jest.fn()} />);
        expect(container.querySelector('svg')).toBeNull();

        await render(<StatTile label="Donations" value="1" state="ready" />);
        expect(container.querySelector('svg')).toBeNull();
    });

    it('is not a button without onToggle', async () => {
        await render(<StatTile label="Donors" value="12" state="ready" />);

        expect(container.querySelector('button')).toBeNull();
        expect(container.textContent).toContain('12');
    });

    it('shows the skeleton while loading, and a loading text for screen readers', async () => {
        await render(<StatTile label="Donors" state="loading" />);

        expect(container.querySelector('.givewp-skeleton')).not.toBeNull();
        expect(container.querySelector('.screen-reader-text')?.textContent).toBe('Loading');
    });

    it('keeps the old value while refreshing, dimmed, with no live announcement', async () => {
        await render(<StatTile label="Donors" value="12" state="refreshing" />);

        expect(container.querySelector('.givewp-stat-tile__value--refreshing')?.textContent).toBe('12');
        expect(container.querySelector('[aria-live], [role="status"], [role="alert"]')).toBeNull();
    });

    it('shows the upgrade message, not a number, and is never a button', async () => {
        await render(
            <StatTile
                label="Recurring"
                state="upgrade"
                value="9"
                upgrade={{href: 'https://example.test/up', message: 'Grow with recurring.'}}
                onToggle={jest.fn()}
            />
        );

        const link = container.querySelector('a') as HTMLAnchorElement;

        expect(container.querySelector('button')).toBeNull();
        expect(link.getAttribute('href')).toBe('https://example.test/up');
        expect(link.textContent).toBe('Upgrade');
        expect(container.textContent).toContain('Grow with recurring.');
        expect(container.textContent).not.toContain('9');
    });

    it('shows the error dash', async () => {
        await render(<StatTile label="Donors" state="error" />);

        expect(container.querySelector('.givewp-list-table-dash')).not.toBeNull();
        expect(container.textContent).toContain('Statistic could not be loaded');
    });

    it('renders no spinner in any state', async () => {
        for (const state of ['loading', 'ready', 'refreshing', 'error', 'upgrade'] as const) {
            await render(
                <StatTile
                    label="Donors"
                    value="1"
                    state={state}
                    upgrade={{href: 'https://example.test', message: 'm'}}
                />
            );

            expect(container.querySelector('.components-spinner')).toBeNull();
        }
    });
});

import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {StatConfig} from '../ListTablePage/types';
import StatTiles from './index';

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const config: Record<string, StatConfig> = {
    donorsCount: {label: 'Number of Donors'},
    oneTimeDonorsCount: {label: 'One-Time Donors'},
    subscribersCount: {
        label: 'Subscribers',
        upgrade: {href: 'https://example.test/up', tooltip: 'Grow with recurring.'},
    },
};
const stats = {
    values: {donorsCount: 1204, oneTimeDonorsCount: 4, subscribersCount: 0},
    formatted: {donorsCount: '1,204', oneTimeDonorsCount: '4', subscribersCount: '0'},
};

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

describe('StatTiles', () => {
    it('renders every tile as a skeleton while loading, even with no stats yet', async () => {
        await render(<StatTiles config={config} status="loading" label="Donors statistics" />);

        expect(container.querySelectorAll('.givewp-stat-tile')).toHaveLength(3);
        expect(container.querySelectorAll('.givewp-skeleton').length).toBe(2);
        expect(container.querySelector('.components-spinner')).toBeNull();
    });

    it('names the region by the screen, and shows the server text in config order', async () => {
        await render(<StatTiles config={config} stats={stats} status="ready" label="Donors statistics" />);

        const region = container.querySelector('[role="region"]') as HTMLElement;
        const tiles = Array.from(container.querySelectorAll('.givewp-stat-tile'));

        expect(region.getAttribute('aria-label')).toBe('Donors statistics');
        expect(tiles.map((tile) => tile.textContent)).toEqual([
            'Number of Donors1,204',
            'One-Time Donors4',
            expect.stringContaining('Subscribers'),
        ]);
        expect(container.querySelector('button')).toBeNull();
    });

    it('keeps its tiles, with the old values dimmed, while refreshing', async () => {
        await render(<StatTiles config={config} stats={stats} status="refreshing" label="Donors statistics" />);

        expect(container.querySelectorAll('.givewp-stat-tile')).toHaveLength(3);
        expect(container.querySelectorAll('.givewp-stat-tile__value--refreshing')).toHaveLength(2);
    });

    it('shows the upgrade message and link in the tile of a missing add-on', async () => {
        await render(<StatTiles config={config} stats={stats} status="ready" label="Donors statistics" />);

        const link = container.querySelector('a') as HTMLAnchorElement;

        expect(link.getAttribute('href')).toBe('https://example.test/up');
        expect(container.textContent).toContain('Grow with recurring.');
    });

    it('shows the error dash in each tile that has a number to show when loading failed', async () => {
        await render(<StatTiles config={config} status="error" label="Donors statistics" />);

        expect(container.querySelectorAll('.givewp-list-table-dash')).toHaveLength(2);
    });
});

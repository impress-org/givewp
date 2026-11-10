import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import {SWRConfig} from 'swr';
import apiFetch from '@wordpress/api-fetch';
import useListTableStats, {statsStatus} from './useListTableStats';

jest.mock('@wordpress/api-fetch', () => jest.fn());

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;

describe('statsStatus', () => {
    it.each([
        [{hasData: false, hasError: true, isValidating: false}, 'error'],
        [{hasData: true, hasError: true, isValidating: false}, 'ready'],
        [{hasData: false, hasError: false, isValidating: true}, 'loading'],
        [{hasData: false, hasError: true, isValidating: true}, 'loading'],
        [{hasData: false, hasError: false, isValidating: false}, 'loading'],
        [{hasData: true, hasError: false, isValidating: true}, 'refreshing'],
        [{hasData: true, hasError: false, isValidating: false}, 'ready'],
    ])('%j is %s', (input, expected) => {
        expect(statsStatus(input)).toBe(expected);
    });
});

type Result = ReturnType<typeof useListTableStats>;
let root: Root;
let latest: Result;
const seen: string[] = [];

function Harness({endpoint}: {endpoint: string | null}) {
    latest = useListTableStats(endpoint, false);
    if (seen[seen.length - 1] !== latest.status) {
        seen.push(latest.status);
    }

    return null;
}

const render = (endpoint: string | null) =>
    act(async () =>
        root.render(
            <SWRConfig value={{provider: () => new Map(), dedupingInterval: 0}}>
                <Harness endpoint={endpoint} />
            </SWRConfig>
        )
    );
const flush = () => act(async () => {});

beforeEach(() => {
    mockedFetch.mockReset();
    seen.length = 0;
    root = createRoot(document.createElement('div'));
});

afterEach(() => {
    act(() => root.unmount());
});

describe('useListTableStats', () => {
    it('reads the numbers and the server text', async () => {
        mockedFetch.mockResolvedValue({donorsCount: 1204, formatted: {donorsCount: '1,204'}});
        await render('/stats');
        await flush();

        expect(latest.status).toBe('ready');
        expect(latest.stats).toEqual({values: {donorsCount: 1204}, formatted: {donorsCount: '1,204'}});
    });

    it('falls back to the plain number when the server sent no formatted text', async () => {
        mockedFetch.mockResolvedValue({donorsCount: 1204});
        await render('/stats');
        await flush();

        expect(latest.stats).toEqual({values: {donorsCount: 1204}, formatted: {donorsCount: '1204'}});
    });

    it('fetches nothing without an endpoint', async () => {
        await render(null);
        await flush();

        expect(mockedFetch).not.toHaveBeenCalled();
        expect(latest.stats).toBeUndefined();
    });

    it('goes error, loading, ready when refreshed after an error', async () => {
        mockedFetch.mockRejectedValueOnce(new Error('fail'));
        await render('/stats');
        await flush();

        expect(latest.status).toBe('error');

        let finish!: (value: unknown) => void;
        mockedFetch.mockReturnValue(new Promise((resolve) => (finish = resolve)));

        let refreshing!: Promise<unknown>;
        await act(async () => {
            refreshing = latest.refresh();
        });

        expect(latest.status).toBe('loading');

        await act(async () => {
            finish({donorsCount: 3, formatted: {donorsCount: '3'}});
            await refreshing;
        });

        expect(seen).toEqual(['loading', 'error', 'loading', 'ready']);
        expect(latest.status).toBe('ready');
    });
});

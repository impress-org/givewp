import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import {resetLabelCache, knownLabel} from '../filters/labelCache';
import useFilterOptions from './useFilterOptions';

jest.mock('@wordpress/api-fetch', () => jest.fn());

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;
const BASE = '/givewp/v3/list-table-filter-options/campaign?screen=donations';

type Result = ReturnType<typeof useFilterOptions>;
let root: Root;
let latest: Result;

function Harness({enabled, tick}: {enabled: boolean; tick: number}) {
    latest = useFilterOptions({source: 'campaign', screen: 'donations', enabled});

    return <span data-tick={tick} />;
}

const render = (enabled: boolean, tick = 0) => act(async () => root.render(<Harness enabled={enabled} tick={tick} />));
const advance = (ms: number) => act(async () => jest.advanceTimersByTime(ms));
const flush = () => act(async () => {});
const paths = () => mockedFetch.mock.calls.map(([options]) => options.path);
const page = (labels: string[], hasMore = false) => ({
    options: labels.map((label, index) => ({value: String(index + 1), label})),
    hasMore,
});

function deferred<T>() {
    let resolve!: (value: T) => void;
    let reject!: (error: Error) => void;
    const promise = new Promise<T>((res, rej) => {
        resolve = res;
        reject = rej;
    });

    return {promise, resolve, reject};
}

beforeEach(() => {
    jest.useFakeTimers();
    mockedFetch.mockReset();
    resetLabelCache();
    root = createRoot(document.createElement('div'));
});

afterEach(() => {
    act(() => root.unmount());
    jest.useRealTimers();
});

describe('useFilterOptions', () => {
    it('does nothing until enabled', async () => {
        await render(false);
        await advance(1000);

        expect(mockedFetch).not.toHaveBeenCalled();
        expect(latest.isLoading).toBe(false);
    });

    it('first open requests page 1 with no search', async () => {
        const pending = deferred<ReturnType<typeof page>>();
        mockedFetch.mockReturnValue(pending.promise);
        await render(true);
        expect(latest.isLoading).toBe(true);
        await act(async () => pending.resolve(page(['Spring appeal'], true)));

        expect(paths()).toEqual([`${BASE}&page=1`]);
        expect(latest.options).toEqual([{value: '1', label: 'Spring appeal'}]);
        expect(latest.hasMore).toBe(true);
        expect(latest.isLoading).toBe(false);
    });

    it('typing waits 300 ms then requests the term', async () => {
        mockedFetch.mockResolvedValue(page(['A']));
        await render(true);
        await flush();
        mockedFetch.mockClear();

        await act(async () => latest.setSearch('sp'));
        expect(latest.search).toBe('sp');
        await advance(299);
        expect(mockedFetch).not.toHaveBeenCalled();
        await advance(1);

        expect(paths()).toEqual([`${BASE}&search=sp&page=1`]);
    });

    it('keeps a pending search when the parent re-renders mid-delay', async () => {
        mockedFetch.mockResolvedValue(page(['A']));
        await render(true);
        await flush();
        mockedFetch.mockClear();

        await act(async () => latest.setSearch('sp'));
        await advance(200);
        await render(true, 1);
        await advance(100);

        expect(paths()).toEqual([`${BASE}&search=sp&page=1`]);
    });

    it('an older answer never replaces a newer search', async () => {
        mockedFetch.mockResolvedValueOnce(page([]));
        await render(true);
        await flush();

        const slow = deferred<ReturnType<typeof page>>();
        const fast = deferred<ReturnType<typeof page>>();
        mockedFetch.mockReturnValueOnce(slow.promise).mockReturnValueOnce(fast.promise);

        await act(async () => latest.setSearch('s'));
        await advance(300);
        await act(async () => latest.setSearch('sm'));
        await advance(300);

        await act(async () => fast.resolve(page(['Small'])));
        await act(async () => slow.resolve(page(['Spring', 'Summer'])));

        expect(latest.options).toEqual([{value: '1', label: 'Small'}]);
    });

    it('a failed load exposes error and retry requests again', async () => {
        mockedFetch.mockRejectedValueOnce(new Error('Nope'));
        await render(true);
        await flush();

        expect(latest.error?.message).toBe('Nope');
        expect(latest.isLoading).toBe(false);

        mockedFetch.mockResolvedValueOnce(page(['A']));
        await act(async () => latest.retry());
        await flush();

        expect(mockedFetch).toHaveBeenCalledTimes(2);
        expect(latest.error).toBeUndefined();
        expect(latest.options).toHaveLength(1);
    });

    it('loadMore appends page 2', async () => {
        mockedFetch.mockResolvedValueOnce({options: [{value: '1', label: 'A'}], hasMore: true});
        await render(true);
        await flush();

        mockedFetch.mockResolvedValueOnce({options: [{value: '2', label: 'B'}], hasMore: false});
        await act(async () => latest.loadMore());
        await flush();

        expect(paths()[1]).toBe(`${BASE}&page=2`);
        expect(latest.options.map((option) => option.value)).toEqual(['1', '2']);
        expect(latest.hasMore).toBe(false);
    });

    it('loaded options feed the label cache', async () => {
        mockedFetch.mockResolvedValue(page(['Spring appeal']));
        await render(true);
        await flush();

        expect(knownLabel('campaign', '1')).toBe('Spring appeal');
    });

    it('ignores an answer that arrives after unmount', async () => {
        const slow = deferred<ReturnType<typeof page>>();
        mockedFetch.mockReturnValue(slow.promise);
        await render(true);
        act(() => root.unmount());
        root = createRoot(document.createElement('div'));

        // No state update after unmount, so no act warning (the console check fails the test).
        await act(async () => slow.resolve(page(['Late'])));
    });
});

import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import apiFetch from '@wordpress/api-fetch';
import {rememberLabels, resetLabelCache} from '../filters/labelCache';
import useFilterOptionLabels from './useFilterOptionLabels';

jest.mock('@wordpress/api-fetch', () => jest.fn());

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const mockedFetch = apiFetch as unknown as jest.Mock;

let root: Root;
let labelFor: (value: string) => string | undefined;
// What the last render saw for id 2, so a missed re-render shows up.
let renderedTwo: string | undefined;

function Harness({values}: {values: string[]}) {
    labelFor = useFilterOptionLabels('campaign', 'donations', values);
    renderedTwo = labelFor('2');

    return null;
}

const render = (values: string[]) => act(async () => root.render(<Harness values={values} />));
const flush = () => act(async () => {});
const paths = () => mockedFetch.mock.calls.map(([options]) => decodeURIComponent(options.path));

beforeEach(() => {
    mockedFetch.mockReset();
    resetLabelCache();
    root = createRoot(document.createElement('div'));
});

afterEach(() => act(() => root.unmount()));

describe('useFilterOptionLabels', () => {
    it('requests include only for ids with no known label, once', async () => {
        rememberLabels('campaign', [{value: '1', label: 'Known'}]);
        mockedFetch.mockResolvedValue({options: [{value: '2', label: 'Two'}], hasMore: false});

        await render(['1', '2']);
        await flush();
        await render(['1', '2']);
        await flush();

        expect(paths()).toHaveLength(1);
        expect(paths()[0]).toContain('/campaign?screen=donations&include[0]=2');
        expect(paths()[0]).not.toContain('include[1]');
        expect(labelFor('2')).toBe('Two');
        expect(labelFor('1')).toBe('Known');
    });

    it('known labels need no request', async () => {
        rememberLabels('campaign', [{value: '1', label: 'Known'}]);

        await render(['1']);
        await flush();

        expect(mockedFetch).not.toHaveBeenCalled();
        expect(labelFor('1')).toBe('Known');
    });

    it('a missing id stays undefined and is not requested again', async () => {
        mockedFetch.mockResolvedValue({options: [], hasMore: false});

        await render(['9']);
        await flush();
        await render(['9', '9']);
        await flush();

        expect(mockedFetch).toHaveBeenCalledTimes(1);
        expect(labelFor('9')).toBeUndefined();
    });

    it('splits more than 50 unknown ids into requests of at most 50', async () => {
        const ids = Array.from({length: 51}, (_, index) => String(index + 1));
        mockedFetch.mockImplementation(({path}: {path: string}) => {
            const asked = Array.from(decodeURIComponent(path).matchAll(/include\[\d+\]=(\d+)/g)).map((m) => m[1]);

            return Promise.resolve({options: asked.map((value) => ({value, label: `L${value}`})), hasMore: false});
        });

        await render(ids);
        await flush();

        const counts = paths().map((path) => (path.match(/include\[/g) ?? []).length);
        expect(counts.sort()).toEqual([1, 50]);
        expect(labelFor('1')).toBe('L1');
        expect(labelFor('51')).toBe('L51');
    });

    it('returns a new lookup function when the cache fills', async () => {
        let resolve!: (page: {options: {value: string; label: string}[]; hasMore: boolean}) => void;
        mockedFetch.mockReturnValue(new Promise((res) => (resolve = res)));
        await render(['2']);
        const before = labelFor;

        await act(async () => resolve({options: [{value: '2', label: 'Two'}], hasMore: false}));

        expect(labelFor).not.toBe(before);
        expect(labelFor('2')).toBe('Two');
    });

    it('still shows a label that arrives after the values changed', async () => {
        let resolve!: (page: {options: {value: string; label: string}[]; hasMore: boolean}) => void;
        mockedFetch.mockReturnValue(new Promise((res) => (resolve = res)));
        rememberLabels('campaign', [{value: '1', label: 'One'}]);

        await render(['2']);
        await render(['1']);
        await act(async () => resolve({options: [{value: '2', label: 'Two'}], hasMore: false}));

        expect(renderedTwo).toBe('Two');
    });

    it('does not request the none value', async () => {
        await render(['none']);
        await flush();

        expect(mockedFetch).not.toHaveBeenCalled();
    });
});

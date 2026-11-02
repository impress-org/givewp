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

function Harness({values}: {values: string[]}) {
    labelFor = useFilterOptionLabels('campaign', 'donations', values);

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

    it('does not request the none value', async () => {
        await render(['none']);
        await flush();

        expect(mockedFetch).not.toHaveBeenCalled();
    });
});

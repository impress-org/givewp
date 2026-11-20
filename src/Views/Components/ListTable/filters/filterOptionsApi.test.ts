import apiFetch from '@wordpress/api-fetch';
import {fetchFilterOptions} from './filterOptionsApi';

jest.mock('@wordpress/api-fetch', () => jest.fn());

const mockedFetch = apiFetch as unknown as jest.Mock;

beforeEach(() => mockedFetch.mockReset());

describe('fetchFilterOptions', () => {
    it('asks for the first page when it is given no page', async () => {
        mockedFetch.mockResolvedValue({options: [], hasMore: false});

        await fetchFilterOptions({source: 'campaign', screen: 'donations'});

        expect(decodeURIComponent(mockedFetch.mock.calls[0][0].path)).toContain('page=1');
    });

    it('looks up ids without a page', async () => {
        mockedFetch.mockResolvedValue({options: [], hasMore: false});

        await fetchFilterOptions({source: 'campaign', screen: 'donations', include: ['5', '6']});

        const path = decodeURIComponent(mockedFetch.mock.calls[0][0].path);

        expect(path).toContain('include[0]=5');
        expect(path).not.toContain('page=');
    });

    it('sends no request for an empty list of ids', async () => {
        await expect(fetchFilterOptions({source: 'campaign', screen: 'donations', include: []})).resolves.toEqual({
            options: [],
            hasMore: false,
        });

        expect(mockedFetch).not.toHaveBeenCalled();
    });
});

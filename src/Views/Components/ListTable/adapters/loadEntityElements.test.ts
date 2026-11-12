const mockGetEntityRecords = jest.fn();
const mockCanUser = jest.fn(async () => true);

jest.mock('@wordpress/data', () => ({
    resolveSelect: () => ({getEntityRecords: mockGetEntityRecords, canUser: mockCanUser}),
}));
jest.mock('@wordpress/core-data', () => ({store: 'core'}));

import {loadEntityElements} from './loadEntityElements';

const campaigns = (from: number, count: number) =>
    Array.from({length: count}, (_, index) => ({id: from + index, title: `Campaign ${from + index}`}));

describe('loadEntityElements', () => {
    beforeEach(() => mockGetEntityRecords.mockReset());

    it('reads every page until a short page', async () => {
        mockGetEntityRecords.mockResolvedValueOnce(campaigns(1, 100)).mockResolvedValueOnce(campaigns(101, 5));

        const options = await loadEntityElements('campaign');

        expect(options).toHaveLength(105);
        expect(options[104]).toEqual({value: 105, label: 'Campaign 105'});
        expect(mockGetEntityRecords).toHaveBeenNthCalledWith(
            2,
            'givewp',
            'campaign',
            expect.objectContaining({page: 2, per_page: 100})
        );
    });

    it('stops at the page past the last one, which the post type route answers with an error', async () => {
        mockGetEntityRecords
            .mockResolvedValueOnce(campaigns(1, 100))
            .mockRejectedValueOnce({code: 'rest_post_invalid_page_number'});

        await expect(loadEntityElements('form')).resolves.toHaveLength(100);
    });
});

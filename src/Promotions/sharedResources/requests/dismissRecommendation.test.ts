import dismissRecommendation from './dismissRecommendation';

const fetchMock = jest.fn();

beforeEach(() => {
    fetchMock.mockReset();
    global.fetch = fetchMock as unknown as typeof fetch;
});

describe('dismissRecommendation', () => {
    it('posts the option with the nonce and returns true when the request works', async () => {
        fetchMock.mockResolvedValue({ok: true});

        await expect(dismissRecommendation('some_option', 'nonce-1')).resolves.toBe(true);

        expect(fetchMock).toHaveBeenCalledWith(
            '/wp-json/give-api/v2/admin/recommended-options',
            expect.objectContaining({
                method: 'POST',
                headers: expect.objectContaining({'X-WP-Nonce': 'nonce-1'}),
                body: JSON.stringify({option: 'some_option'}),
            })
        );
    });

    it('returns false when the server answers with an error status', async () => {
        fetchMock.mockResolvedValue({ok: false, status: 500});

        await expect(dismissRecommendation('some_option', 'nonce-1')).resolves.toBe(false);
    });

    it('returns false, and does not throw, when the request itself fails', async () => {
        fetchMock.mockRejectedValue(new TypeError('Network error'));

        await expect(dismissRecommendation('some_option', 'nonce-1')).resolves.toBe(false);
    });
});

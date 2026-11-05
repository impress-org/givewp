import {act} from 'react';
import {createRoot, type Root} from 'react-dom/client';
import type {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';
import dismissRecommendation from '@givewp/promotions/requests/dismissRecommendation';
import type {ListTableRecommendations} from '../notices/types';
import useDailyRecommendation from './useDailyRecommendation';

jest.mock('@givewp/promotions/requests/dismissRecommendation', () => ({__esModule: true, default: jest.fn()}));
jest.mock('../filters/siteDate', () => ({siteToday: () => '2026-11-02'}));

(globalThis as {IS_REACT_ACT_ENVIRONMENT?: boolean}).IS_REACT_ACT_ENVIRONMENT = true;

const option = (name: string): RecommendedProductData => ({
    enum: `givewp_donations_${name}_recommendation_dismissed` as RecommendedProductData['enum'],
    documentationPage: `https://example.test/${name}`,
    message: name,
    innerHtml: name,
});

const config: ListTableRecommendations = {
    options: [option('recurring'), option('fee_recovery'), option('designated_funds')],
    dismissed: [],
    apiNonce: 'nonce-1',
};

let result: ReturnType<typeof useDailyRecommendation>;
function Probe({value}: {value?: ListTableRecommendations}) {
    result = useDailyRecommendation(value);
    return null;
}

let container: HTMLDivElement;
let root: Root;

beforeEach(() => {
    (dismissRecommendation as jest.Mock).mockReset().mockResolvedValue(true);
    container = document.createElement('div');
    root = createRoot(container);
});

afterEach(async () => {
    await act(() => root.unmount());
});

describe('useDailyRecommendation', () => {
    it('returns null without a config', async () => {
        await act(() => root.render(<Probe />));

        expect(result.recommendation).toBeNull();
    });

    it("picks today's one", async () => {
        await act(() => root.render(<Probe value={config} />));

        const index = Math.floor(Date.parse('2026-11-02T00:00:00Z') / 86400000) % 3;
        expect(result.recommendation).toBe(config.options[index]);
    });

    it('hides at once on dismiss, stores it, and does not pick another until the next load', async () => {
        await act(() => root.render(<Probe value={config} />));
        const shown = result.recommendation as RecommendedProductData;

        await act(async () => result.dismiss());

        expect(result.recommendation).toBeNull();
        expect(dismissRecommendation).toHaveBeenCalledTimes(1);
        expect(dismissRecommendation).toHaveBeenCalledWith(shown.enum, 'nonce-1');

        // A re-render with the same props must not bring another one in.
        await act(() => root.render(<Probe value={{...config}} />));
        expect(result.recommendation).toBeNull();
    });

    it('brings the same one back when storing the dismissal fails', async () => {
        (dismissRecommendation as jest.Mock).mockResolvedValue(false);
        await act(() => root.render(<Probe value={config} />));
        const shown = result.recommendation as RecommendedProductData;

        await act(async () => result.dismiss());

        expect(result.recommendation).toBe(shown);
    });
});

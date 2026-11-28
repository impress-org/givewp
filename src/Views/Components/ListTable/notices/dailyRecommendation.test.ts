import type {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';
import {pickDailyRecommendation} from './dailyRecommendation';

const option = (name: string): RecommendedProductData => ({
    enum: `givewp_donations_${name}_recommendation_dismissed` as RecommendedProductData['enum'],
    documentationPage: `https://example.test/${name}`,
    message: name,
    innerHtml: name,
});

const three = [option('recurring'), option('fee_recovery'), option('designated_funds')];

describe('pickDailyRecommendation', () => {
    it('shows the same one all day', () => {
        expect(pickDailyRecommendation(three, [], '2026-11-02')).toBe(pickDailyRecommendation(three, [], '2026-11-02'));
    });

    it('moves to the next one the next day and wraps', () => {
        const days = ['2026-11-02', '2026-11-03', '2026-11-04', '2026-11-05'].map(
            (day) => pickDailyRecommendation(three, [], day)?.enum
        );

        expect(new Set(days.slice(0, 3)).size).toBe(3);
        expect(days[3]).toBe(days[0]);
    });

    it('skips dismissed ones', () => {
        const dismissed = [three[0].enum, three[1].enum];

        for (const day of ['2026-11-02', '2026-11-03', '2026-11-04']) {
            expect(pickDailyRecommendation(three, dismissed, day)).toBe(three[2]);
        }
    });

    it('returns null when all are dismissed or there are none', () => {
        expect(pickDailyRecommendation(three, three.map((o) => o.enum), '2026-11-02')).toBeNull();
        expect(pickDailyRecommendation([], [], '2026-11-02')).toBeNull();
    });
});

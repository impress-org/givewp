import type {RecommendedProductData} from '@givewp/promotions/hooks/useRecommendations';

const MS_PER_DAY = 86400000;

/**
 * Picks the one recommendation to show on a day. The pick depends only on the options that are
 * left and the day, so it stays the same all day and moves to the next option the next day.
 *
 * @since TBD
 *
 * @param options   The screen's options, in order.
 * @param dismissed Enums of the dismissed options.
 * @param day       A `YYYY-MM-DD` day in the site's time zone.
 */
export function pickDailyRecommendation(
    options: RecommendedProductData[],
    dismissed: string[],
    day: string
): RecommendedProductData | null {
    const available = options.filter((option) => !dismissed.includes(option.enum));

    if (available.length === 0) {
        return null;
    }

    const dayNumber = Math.floor(Date.parse(`${day}T00:00:00Z`) / MS_PER_DAY);

    return available[dayNumber % available.length];
}

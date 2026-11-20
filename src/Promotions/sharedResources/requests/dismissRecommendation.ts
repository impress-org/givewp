/**
 * Stores that a recommendation was dismissed. Resolves to true when the server saved it, and to
 * false when the request failed or the server answered with an error. It never rejects, so callers
 * that ignore the result do not get an unhandled error.
 *
 * @since TBD Return whether the dismissal was saved, and resolve to false on failure.
 * @since 2.27.1
 */
export default async function dismissRecommendation(option: string, nonce: string): Promise<boolean> {
    const url = '/wp-json/give-api/v2/admin/recommended-options';

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': nonce,
            },
            body: JSON.stringify({
                option,
            }),
        });

        return response.ok;
    } catch {
        return false;
    }
}

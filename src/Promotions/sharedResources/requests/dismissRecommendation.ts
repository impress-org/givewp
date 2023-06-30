/**
 *
 * @since 2.27.1
 *
 */

export default async function dismissRecommendation(option: string, nonce: string) {
    const url = '/wp-json/give-api/v2/admin/recommended-options';

    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce,
        },
        body: JSON.stringify({
            option
        }),
    });
}

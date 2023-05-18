/**
 *
 * @unreleased
 *
 */

export default async function dismissRecommendation(option: string, apiRoot: string, nonce: string) {
    const url = `${apiRoot}/admin/recommended-options`;

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

import getWindowData from '../../index';

export default async function dismissWelcomeBanner() {
    const url = '/wp-json/give-api/v2/admin/welcome-banner';
    const {nonce, action} = getWindowData();

    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce,
        },
        body: JSON.stringify({
            action: action,
        }),
    });
}

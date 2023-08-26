import getWindowData from '../../index';

export default async function dismissWelcomeBanner() {
    const {nonce, action, root} = getWindowData();
    const url = `${root}/dismiss`;

    await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': nonce,
        },
        body: JSON.stringify({
            action,
        }),
    });
}

import getWindowData from '../../index';
import {useState} from 'react';

export function useDismiss() {
    const [showBanner, setShowBanner] = useState<boolean>(true);

    const dismissBanner = async function () {
        const {nonce, action, root} = getWindowData();
        const url = `${root}/dismiss`;
        
        setShowBanner(false);

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
    };

    return {dismissBanner, showBanner};
}

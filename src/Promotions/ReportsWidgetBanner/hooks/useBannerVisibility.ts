import { useState, useEffect } from 'react';
import {getWidgetWindowData} from "../window/widgetWindow";


export const useBannerVisibility = () => {
    const windowData = getWidgetWindowData();
    const [isVisible, setIsVisible] = useState<boolean>(false);

    useEffect(() => {
            setIsVisible(!!windowData?.banner);
    }, [windowData?.banner]);

    const hideWidgetBanner = async (id) => {
        const formData = new FormData();
        formData.append('id', id);

        try {
             await fetch(`${windowData.apiRoot}/hide`, {
                method: 'POST',
                headers: {
                    'X-WP-Nonce': windowData.apiNonce,
                },
                body: formData,
            });

            setIsVisible(false);
        } catch (error) {
            console.error('Error hiding banner:', error);
        }
    };

    return {
        isVisible,
        hideWidgetBanner,
    };
};

export default useBannerVisibility;

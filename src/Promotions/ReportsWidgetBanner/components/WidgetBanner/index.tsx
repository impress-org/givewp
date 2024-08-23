import {__} from '@wordpress/i18n';

import './styles.scss';
import {getWidgetWindowData} from "../../window/widgetWindow";


type widgetBannerProps = {
    hideWidgetBanner: (id) => void;
};

export default function WidgetBanner({hideWidgetBanner}: widgetBannerProps) {
    const banner = getWidgetWindowData().banner;

    const dismissBanner = () => {
        hideWidgetBanner(banner.id);
    }

    return (
        <div id={`givewp-sale-banner-${banner.id}`} className={'givewp-reports-widget-banner'}>
            <div className={'givewp-reports-widget-banner__header'}>
                <h1 className={'givewp-reports-widget-banner__header__main'}>{__('Make it yours.', 'give')}</h1>
                <h2 className={'givewp-reports-widget-banner__header__secondary'}>{__('Save 40% on all GiveWP products', 'give')}</h2>
            </div>
            <a className={'givewp-reports-widget-banner__cta'}
               href={banner.actionUrl}
               target={"_blank"}
               rel={"noopener noreferrer"}
            >
                {banner.actionText}
            </a>
            <button
                onClick={dismissBanner}
                type="button"
                aria-label={__('Dismiss', 'give')}
                aria-controls={`givewp-sale-banner-${banner.id}`}
                className={'givewp-reports-widget-banner__dismiss'}
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="15px" height="14px" viewBox="0 0 20 19" fill="none">
                    <line x1="1.35355" y1="0.646447" x2="19.3535" y2="18.6464" stroke="#F9FAF9" />
                    <line
                        y1="-0.5"
                        x2="25.4558"
                        y2="-0.5"
                        transform="matrix(0.707107 -0.707106 0.707107 0.707106 1 19)"
                        stroke="#F9FAF9"
                    />
                </svg>
            </button>
        </div>
    );
}

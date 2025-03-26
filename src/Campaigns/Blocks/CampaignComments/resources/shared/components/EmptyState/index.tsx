import React from 'react';
import {__} from '@wordpress/i18n';
import './styles.scss';

export default function EmptyState() {
    return (
        <div className="givewp-campaign-comments-block-empty-state">
            <div className="givewp-campaign-donations-block__empty-state__icon">
                <svg width="40" height="40" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M10 33.48h4.351c.567 0 1.131.068 1.681.203l4.597 1.117a7.126 7.126 0 0 0 3.044.07l5.083-.988a7.007 7.007 0 0 0 3.545-1.846l3.596-3.498a2.505 2.505 0 0 0 0-3.615c-.924-.9-2.389-1-3.434-.238l-4.191 3.058c-.6.439-1.33.675-2.082.675h-4.047 2.576c1.452 0 2.628-1.144 2.628-2.557v-.511c0-1.173-.82-2.195-1.99-2.479l-3.975-.967a8.374 8.374 0 0 0-1.976-.236c-1.608 0-4.519 1.332-4.519 1.332l-4.886 2.043m-6.667-.708V34c0 .934 0 1.4.182 1.757.16.314.414.569.728.728.357.182.823.182 1.757.182h1.333c.933 0 1.4 0 1.757-.181.313-.16.568-.415.728-.729.182-.356.182-.823.182-1.757v-9.666c0-.934 0-1.4-.182-1.757a1.667 1.667 0 0 0-.728-.728c-.357-.182-.824-.182-1.757-.182H6.001c-.934 0-1.4 0-1.757.182-.314.16-.569.415-.728.728-.182.357-.182.823-.182 1.757zM28.653 5.989c-.995-2.082-3.288-3.185-5.518-2.12-2.23 1.064-3.18 3.588-2.247 5.804.577 1.37 2.23 4.028 3.41 5.86.435.677.653 1.015.971 1.213.273.17.614.262.936.251.374-.012.732-.197 1.448-.565 1.936-.997 4.698-2.473 5.882-3.37 1.917-1.452 2.392-4.121.957-6.15s-3.937-2.229-5.84-.923z"
                        stroke="#0BD972"
                        stroke-width="3.333"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>
            <div className="givewp-campaign-comments-block-empty-state__details">
                <strong className="givewp-campaign-comments-block-empty-state__title">
                    {__('Leave a supportive message by', 'give')}
                </strong>
                <p className="givewp-campaign-comments-block-empty-state__description">
                    {__('donating to the campaign.', 'give')}
                </p>
            </div>
        </div>
    );
}

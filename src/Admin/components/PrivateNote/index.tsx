import {__} from '@wordpress/i18n';
import React from 'react';

/**
 * @unreleased
 */
export default function PrivateNote() {
    return (
        <div style={{margin: '0 auto'}}>
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                    d="M28 22H16m4 8h-4m16-16H16m24-.4v20.8c0 3.36 0 5.04-.654 6.324a6 6 0 0 1-2.622 2.622C35.44 44 33.76 44 30.4 44H17.6c-3.36 0-5.04 0-6.324-.654a6 6 0 0 1-2.622-2.622C8 39.44 8 37.76 8 34.4V13.6c0-3.36 0-5.04.654-6.324a6 6 0 0 1 2.622-2.622C12.56 4 14.24 4 17.6 4h12.8c3.36 0 5.04 0 6.324.654a6 6 0 0 1 2.622 2.622C40 8.56 40 10.24 40 13.6z"
                    stroke="#9CA0AF"
                    strokeWidth="4"
                    strokeLinecap="round"
                    strokeLinejoin="round"
                />
            </svg>
            <p>{__('No notes yet', 'give')}</p>
        </div>
    );
}

import {ReactNode} from 'react';
import {Path, SVG} from '@wordpress/components';

type PanelDescription = {
    title: string;
    children: ReactNode;
    description: string;
};

export default function DesignSettings({title, description, children}: PanelDescription) {
    return (
        <div className={'givewp-block-editor-design-sidebar__settings'}>
            <div className="block-editor-block-inspector">
                <div className="block-editor-block-card">
                    <span className="block-editor-block-icon has-colors">
                        <SVG xmlns="http://www.w3.org/2000/SVG" width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <Path
                                fillRule="evenodd"
                                clipRule="evenodd"
                                d="M11.9287 18C15.2424 18 17.9287 15.3137 17.9287 12C17.9287 8.68629 15.2424 6 11.9287 6C8.615 6 5.92871 8.68629 5.92871 12C5.92871 15.3137 8.615 18 11.9287 18ZM11.9287 15C13.5856 15 14.9287 13.6569 14.9287 12C14.9287 10.3431 13.5856 9 11.9287 9C10.2719 9 8.92871 10.3431 8.92871 12C8.92871 13.6569 10.2719 15 11.9287 15Z"
                                fill="#1E1E1E"
                            />
                            <Path
                                fillRule="evenodd"
                                clipRule="evenodd"
                                d="M11.2758 4C10.787 4 10.3698 4.35341 10.2894 4.8356L9.92871 7H13.9287L13.568 4.8356C13.4876 4.35341 13.0704 4 12.5816 4H11.2758ZM12.5816 20C13.0704 20 13.4876 19.6466 13.568 19.1644L13.9287 17H9.92871L10.2894 19.1644C10.3698 19.6466 10.787 20 11.2758 20H12.5816Z"
                                fill="#1E1E1E"
                            />
                            <Path
                                fillRule="evenodd"
                                clipRule="evenodd"
                                d="M18.53 7.43422C18.2856 7.01088 17.7709 6.82629 17.3132 6.99778L15.2584 7.76758L17.2584 11.2317L18.9524 9.83708C19.3298 9.52638 19.4273 8.98838 19.1829 8.56503L18.53 7.43422ZM5.32647 16.565C5.57089 16.9884 6.08555 17.173 6.54332 17.0015L8.59811 16.2317L6.59811 12.7676L4.90406 14.1622C4.52665 14.4729 4.42918 15.0109 4.6736 15.4342L5.32647 16.565Z"
                                fill="#1E1E1E"
                            />
                            <Path
                                fillRule="evenodd"
                                clipRule="evenodd"
                                d="M4.67454 8.56578C4.43012 8.98912 4.52759 9.52713 4.90499 9.83782L6.59905 11.2324L8.59905 7.76832L6.54426 6.99852C6.08649 6.82703 5.57183 7.01162 5.32741 7.43497L4.67454 8.56578ZM19.1838 15.435C19.4282 15.0116 19.3308 14.4736 18.9534 14.1629L17.2593 12.7683L15.2593 16.2324L17.3141 17.0022C17.7719 17.1737 18.2865 16.9891 18.5309 16.5658L19.1838 15.435Z"
                                fill="#1E1E1E"
                            />
                        </SVG>
                    </span>
                    <div className="block-editor-block-card__content">
                        <h4 className="block-editor-block-card__title">{title}</h4>
                        <span className="block-editor-block-card__description">{description}</span>
                    </div>
                </div>
                {children}
            </div>
        </div>
    );
}

import {createRoot} from '@wordpress/element';
import {CampaignGridType} from './types';
import App from './app/index';

/**
 * @since 4.0.0
 */
const nodeList = document.querySelectorAll('[data-givewp-campaign-grid]');

if (nodeList) {
    const containers = Array.from(nodeList);

    containers.map((container: any) => {
        const attributes: CampaignGridType = JSON.parse(container.dataset?.attributes);
        const root = createRoot(container);
        return root.render(<App attributes={attributes} />);
    });
}

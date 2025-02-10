import {render} from '@wordpress/element';
import {CampaignListType} from './types';
import App from './app/index';

/**
 * @unreleased
 */
const nodeList = document.querySelectorAll('.give-campaigns-campaignListBlock-container');

if (nodeList) {
    const containers = Array.from(nodeList);

    containers.map((container: any) => {
        const attributes: CampaignListType = JSON.parse(container.dataset?.attributes);
        return render(<App attributes={attributes} />, container);
    });
}

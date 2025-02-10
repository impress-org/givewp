import {render} from '@wordpress/element';
import useCampaign from '../shared/hooks/useCampaign';
import App from './app/index';

const BlockApp = ({campaignId}: { campaignId: number }) => {
    const {campaign, hasResolved} = useCampaign(campaignId);

    if (!hasResolved || !campaignId) {
        return null;
    }

    return <App campaign={campaign} />;
}

/**
 * @unreleased
 */
const nodeList = document.querySelectorAll('.give-campaigns-goalBlock-container');

if (nodeList) {
    const containers = Array.from(nodeList);

    containers.map((container: any) => {
        return render(<BlockApp campaignId={container.dataset?.id} />, container);
    });
}

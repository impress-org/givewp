import {createRoot} from '@wordpress/element';
import useCampaign from '../shared/hooks/useCampaign';
import App from './app/index';

export const CampaignGoalBlockApp = ({campaignId}: { campaignId: number }) => {
    const {campaign, hasResolved} = useCampaign(campaignId);

    if (!hasResolved || !campaignId) {
        return null;
    }

    return <App campaign={campaign} />;
}

/**
 * @since 4.0.0
 */
const nodeList = document.querySelectorAll('[data-givewp-campaign-goal]');

if (nodeList) {
    const containers = Array.from(nodeList);

    containers.map((container: any) => {
        const root = createRoot(container);
        return root.render(<CampaignGoalBlockApp campaignId={container.dataset?.id} />);
    });
}

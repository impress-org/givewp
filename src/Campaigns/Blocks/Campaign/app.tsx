import {createRoot} from '@wordpress/element';
import {CampaignBlockType} from './types';
import useCampaign from '../shared/hooks/useCampaign';
import CampaignCard from '../shared/components/CampaignCard';

export const CampaignBlockApp = ({attributes}: { attributes: CampaignBlockType }) => {
    const {campaign, hasResolved} = useCampaign(attributes?.campaignId);

    if (!hasResolved) {
        return null;
    }

    return (
        <CampaignCard
            campaign={campaign}
            showImage={attributes?.showImage}
            showDescription={attributes?.showDescription}
            showGoal={attributes?.showGoal}
        />
    );
}

/**
 * @since 4.0.0
 */
const nodeList = document.querySelectorAll('[data-givewp-campaign-block]');

if (nodeList) {
    const containers = Array.from(nodeList);

    containers.map((container: any) => {
        const attributes: CampaignBlockType = JSON.parse(container.dataset?.attributes);
        const root = createRoot(container);
        return root.render(<CampaignBlockApp attributes={attributes} />)
    });
}

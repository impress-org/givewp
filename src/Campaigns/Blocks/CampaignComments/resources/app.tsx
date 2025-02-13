import {createRoot} from '@wordpress/element';
import CampaignComments from './shared/components/CampaignComments';
import getGiveCampaignCommentsBlockWindowData from "./shared/window";

const roots = document.querySelectorAll('.givewp-campaign-comment-block');

(function render() {
    return roots.forEach((root) => {
        const attributes = root.getAttribute('data-attributes');
        const comments = getGiveCampaignCommentsBlockWindowData();

        createRoot(root).render(<CampaignComments attributes={JSON.parse(attributes)} comments={comments} />);
    });
})();

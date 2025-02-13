import {createRoot} from '@wordpress/element';
import CampaignComments from './shared/components/CampaignComments';

const roots = document.querySelectorAll('.givewp-campaign-comment-block');

if (roots) {
    roots.forEach((root) => {
        const attributes = root.getAttribute('data-attributes');

        return createRoot(root).render(<CampaignComments attributes={JSON.parse(attributes)} />);
    });
}

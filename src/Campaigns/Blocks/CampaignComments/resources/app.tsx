import {createRoot} from '@wordpress/element';
import CampaignComments from './shared/components/CampaignComments';

const roots = document.querySelectorAll('.givewp-campaign-comment-block');

(function render() {
    return roots.forEach((root) => {
        const attributes = root.getAttribute('data-attributes');

        createRoot(root).render(<CampaignComments attributes={JSON.parse(attributes)} />);
    });
})();
